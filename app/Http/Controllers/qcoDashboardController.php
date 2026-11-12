<?php

namespace App\Http\Controllers;

use App\Models\qco;
use App\Models\cell_info;
use App\Models\qco___status;
use App\Models\qco___preparation;
use App\Models\qco___preparation_files;
use App\Models\qco___preparation_list;
use App\Models\User;
use App\Models\PlanMaster;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Services\QcoService;

class qcoDashboardController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
            // Ambil data user yang sedang login
        $user = Auth::user();
        $user->load('department');
        $department = $user->department->nama;
        // Cek manual department_id (misal: hanya departemen dengan ID 1 yang boleh akses)

        $currentMonth = $request->input('month') ? $request->input('month') . '-01' : Carbon::now()->format('Y-m-d');
        $cell = $request->input('cell') ? $request->input('cell'): "";
        $get_user = User::get();
        // Ambil data qco berdasarkan rentang bulan yang sedang aktif di kalender
        $start = Carbon::parse($currentMonth)->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = Carbon::parse($currentMonth)->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $qcoList = qco::with(['qco_status','user', 'user.department', 'user.role','preparations','cell_info','preparations.preparation_file'])
                ->whereBetween('change_over_date', [$start, $end])
                 ->whereHas('user.department', function ($query) {
                        // Ganti 'PLANNER' dengan nama department yang ingin dicari
                        $query->where('nama', 'PLANNER');
                    })
                ->when($cell, function ($query, $cell) {
                        return $query->where('cell', $cell);
                    })
                ->get();
        // 2. Filter / buang data berdasarkan department dan stage-nya
        if ($department === 'DEVELOPMENT') {
            $qcoList = $qcoList->reject(function ($item) {
                return $item->stage === 'Mass Production';
            });
        }

        if ($department === 'PE') {
            $qcoList = $qcoList->reject(function ($item) {
                return $item->stage === 'Production Trial';
            });
        }
        $list = qco___preparation_list::with('department')->get();

        $selectedIds = $qcoList->flatMap(function ($qco) {
            return $qco->preparations;
        })->pluck('id_list')->unique();

        // 2. Petakan $list master sekaligus tandai mana yang sudah ter-checklist
        $list_checklist = $list->map(function ($item) use ($selectedIds) {
            $item->is_checked = $selectedIds->contains($item->id);
            return $item;
        });

        $cell = cell_info::get();
        $planMaster= PlanMaster::whereBetween('stitching_start_date', [$start, $end])->get();
        // return $planMaster;
        return view('qco.index', compact('qcoList', 'currentMonth','cell','list','planMaster'));
    }
    public function dataModal(Request $request)
    {
        $id_qco = $request->input('id_qco');

        // 1. Ambil data QCO sekaligus relasinya
        $qcoRecord = qco::with([
            'qco_status',
            'preparations.preparation_list.department',
            'preparations.preparation_file'
        ])->where('id', $id_qco)->first();

        if (!$qcoRecord) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QCO tidak terdaftar atau Data tidak ditemukan',
                'data'    => null
            ], 404);
        }

        // 2. Tentukan kondisi pengecualian berdasarkan stage
        $whereNot = ($qcoRecord->stage === 'Mass Production') ? 'DEVELOPMENT' : 'PE';

        // 3. Ambil SEMUA master list, lalu LANGSUNG filter menggunakan reject()
        // agar keduanya (preparation_list & allMasterLists) bersih dari $whereNot
        $allMasterListsQuery = qco___preparation_list::with('department')->get()
            ->reject(function ($item) use ($whereNot) {
                return optional($item->department)->nama === $whereNot;
            })->values();

        // Untuk dropdown/list biasa yang sudah difilter
        $preparation_list = $allMasterListsQuery;

        // Untuk perhitungan persentase, group data yang sudah bersih dari $whereNot
        $allMasterLists = $allMasterListsQuery->groupBy(function ($item) {
            return $item->department->nama ?? 'Lainnya';
        });

        // 4. Map data preparations yang sudah dipilih
        $mappedPreparations = $qcoRecord->preparations->map(function ($item) {
            return [
                'id'               => $item->id,
                'id_list'          => $item->id_list,
                'list'             => $item->preparation_list->list ?? null,
                'department'       => $item->preparation_list->department->nama ?? 'Lainnya',
                'preparation_file' => $item->preparation_file
            ];
        })->groupBy('department');

        // 5. Gabungkan dan hitung persentase per departemen (hanya untuk departemen yang lolos filter)
        $preparationsChecklistFormatted = [];

        foreach ($allMasterLists as $deptName => $masterItems) {
            $totalMasterInDept = $masterItems->count();
            $completedItemsInDept = isset($mappedPreparations[$deptName])
                ? count($mappedPreparations[$deptName])
                : 0;

            $percentage = $totalMasterInDept > 0
                ? round(($completedItemsInDept / $totalMasterInDept) * 100, 2)
                : 0;

            $preparationsChecklistFormatted[$deptName] = [
                'total_master' => $totalMasterInDept,
                'completed'    => $completedItemsInDept,
                'percentage'   => $percentage,
                'items'        => $mappedPreparations[$deptName] ?? []
            ];
        }

        // 6. Susun data utama
        $simplifiedData = [
            'id'                     => $qcoRecord->id,
            'nik'                    => $qcoRecord->nik,
            'change_over_date'       => $qcoRecord->change_over_date,
            'model'                  => $qcoRecord->model,
            'completion_date'        => $qcoRecord->completion_date,
            'cell'                   => $qcoRecord->cell,
            'description'            => $qcoRecord->description,
            'stage'                  => $qcoRecord->stage,
            'completion'             => $qcoRecord->completion,
            'status'                 => $qcoRecord->status,
            'actual_change_over'     => $qcoRecord->qco_status->actual_change_over,
            'preparations_checklist' => $preparationsChecklistFormatted
        ];

        return response()->json([
            'status'             => 'success',
            'dataQCO'            => $simplifiedData,
            'preparation_list'   => $preparation_list
        ]);
    }
    public function update_act_co(Request $request) {
        // 1. Tambahkan validasi sederhana (opsional tapi disarankan)
        $request->validate([
            'id_qco' => 'required',
            'act_co' => 'required', // sesuaikan tipenya jika perlu (misal: numeric)
        ]);

        $act_co = $request->act_co;
        $id_qco = $request->id_qco;

        try {
            $qco_status = qco___status::where('id_qco', $id_qco)->first();

            if (!$qco_status) {
                // Jika data tidak ditemukan, rollback atau langsung return 404/500.
                // Karena belum ada perubahan database, commit/rollback belum wajib, tapi return response sudah tepat.
                return response()->json([
                    'success' => false,
                    'message' => 'QCO TIDAK TERDAFTAR!'
                ], 404); // Sebaiknya gunakan 404 (Not Found) daripada 500 (Server Error) untuk data tidak ditemukan
            }

            $qco_status->update([
                'actual_change_over' => $act_co
            ]);

            // Simpan permanen ke database
            return response()->json([
                'success' => true,
                'message' => 'Data actual change over sudah di perbaharui!'
            ]);

        } catch (\Exception $e) {
            // Batalkan semua perubahan jika terjadi error
            // Perbaiki nama method pada log agar sesuai
            Log::error('Error update_act_co: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function sendReminder(Request $request)
    {
        try {
            // Menjalankan Artisan command
            Artisan::call('qco:send-morning-reminder');

            return response()->json([
                'status' => 'success',
                'message' => 'Reminder berhasil dikirim!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim reminder: ' . $e->getMessage()
            ], 500);
        }
    }
    public function cancelQCO(Request $request, QcoService $qcoService) {
        $id_qco = $request->id_qco;

        try {
            // 1. Ambil data beserta relasinya
            $qco = qco::with('cell_info')->where('id', $id_qco)->first();

            if (!$qco) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data QCO tidak ditemukan!'
                ], 404);
            }

            // 2. Lakukan update status
            $qco->update(['status' => 'CANCEL']);

            // 3. Refresh dan ubah ke array (gunakan variabel $qco yang sudah ada)
            $qcoArray = $qco->fresh(['cell_info'])->toArray();

            // 4. Panggil service untuk kirim pesan/notifikasi pembatalan
            $qcoService->messageCancel($qcoArray);

            // 5. Kembalikan response JSON di bagian paling akhir
            return response()->json([
                'status' => 'success',
                'message' => 'QCO sudah di batalkan!',
                'data' => $qcoArray // Opsional: jika ingin mengirimkan datanya ke frontend
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan QCO: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateStatusCancel(Request $request) {
        try {
            // Ambil nilai completion saja dari database untuk efisiensi memory
            $qco = qco::select('completion')->where('id', $request->id_qco)->first();

            if (!$qco) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $status = ($qco->completion == 100) ? 'DONE' : 'NOT YET';

            qco::where('id', $request->id_qco)->update(['status' => $status]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status QCO berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan QCO: ' . $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, QcoService $qcoService) {
        $user = Auth::user();
        $edit_model = $request->edit_model;
        $edit_date = $request->edit_date;
        $id_qco_update = $request->id_qco_update;

        $qco = qco::where('id', $id_qco_update)->first();

        if (!$qco) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Ubah status record lama menjadi CANCEL
        qco::where('id', $id_qco_update)->update(['status' => "CANCEL"]);

        try {
            $qcoService->createQco([
                'nik' => $user->nik,
                'change_over_date' => $edit_date,
                'model' => $edit_model,
                'cell' => $qco->cell,
                'stage' => $qco->stage,
                'status_plan' => 'rescheduled', // <-- SET STATUS PLAN
                'parent_id'   => $qco->id,      // <-- ISI PARENT ID DARI ID LAMA
                'option'      => 'Update',
                'model_lama'  => $qco->model,
                'tanggal_lama' => $qco->change_over_date
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data QCO berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui QCO: ' . $e->getMessage()
            ], 500);
        }
    }
}
