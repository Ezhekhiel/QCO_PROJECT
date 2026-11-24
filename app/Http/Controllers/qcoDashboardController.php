<?php

namespace App\Http\Controllers;

use App\Models\qco;
use App\Models\cell_info;
use App\Models\qco___status;
use App\Models\qco___preparation;
use App\Models\qco___preparation_files;
use App\Models\qco___preparation_list;
use App\Models\Holiday;
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

        $currentMonth = $request->input('month') ? $request->input('month') . '-01' : Carbon::now()->format('Y-m-d');

        // 1. Ambil input cell sebagai array (jika kosong, jadikan array kosong)
        $selectedCells = $request->input('cell', []);

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
                // 2. Ubah when() agar menggunakan whereIn jika $selectedCells tidak kosong
                ->when(!empty($selectedCells), function ($query) use ($selectedCells) {
                        return $query->whereIn('cell', $selectedCells);
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

        // Petakan $list master sekaligus tandai mana yang sudah ter-checklist
        $list_checklist = $list->map(function ($item) use ($selectedIds) {
            $item->is_checked = $selectedIds->contains($item->id);
            return $item;
        });

        // Perhatian: Variabel $cell untuk master data dropdown list
        $cell = cell_info::get();

        $planMaster = PlanMaster::whereBetween('stitching_start_date', [$start, $end])->get();

        return view('qco.index', compact('qcoList', 'currentMonth', 'cell', 'list', 'planMaster'));
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
                'validation'       => $item->validation,
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
    public function updateValidation(Request $request, $id)
    {
        $preparation = qco___preparation::find($id);

        if (!$preparation) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Update status validation berdasarkan input request (0 atau 1 / true atau false)
        $preparation->validation = filter_var($request->validation, FILTER_VALIDATE_BOOLEAN);
        $preparation->save();

        return response()->json([
            'success' => true,
            'message' => 'Status validation berhasil diperbarui!',
            'data' => $preparation
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
        $qco->update(['status' => 'CANCEL']);

        try {
            $qcoService->createQco([
                'nik' => $user->nik,
                'change_over_date' => $edit_date,
                'model' => $qco->model,
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
    public function test() {
        $today = Carbon::today()->startOfDay();

        // 1. Cek apakah hari ini weekend atau hari libur (termasuk tanggal merah Indonesia di tabel Holiday)
        if ($this->isNonWorkingDay($today)) {
            $liburInfo = $today->isWeekend()
                ? "Weekend (" . $today->translatedFormat('l') . ")"
                : "Hari Libur / Tanggal Merah";

            $msg = "Hari ini adalah {$liburInfo}. Pengingat QCO dibatalkan dan tidak dikirim.";
            $this->info($msg);
            Log::info("QCO Reminder: " . $msg);
            return;
        }

        // Ambil daftar tanggal libur sekali saja agar efisien di dalam loop
        // Rentang cukup lebar: dari 15 hari ke belakang sampai 15 hari ke depan dari hari ini
        $holidays = Holiday::whereBetween('holiday_date', [
            $today->copy()->subDays(15)->toDateString(),
            $today->copy()->addDays(15)->toDateString(),
        ])->pluck('holiday_date')->flip()->all();

        // Rentang query awal kita beri buffer 7 hari ke depan agar QCO yang terhalang libur panjang tetap terjaring
        $queryMaxDate = $today->copy()->addDays(7)->toDateString();

        $qcoList = qco::with(['preparations', 'cell_info'])
            ->where('status', '!=', 'CANCEL')
            ->whereBetween('change_over_date', [$today->toDateString(), $queryMaxDate])
            ->orderBy('change_over_date', 'asc')
            ->get();

        if ($qcoList->isEmpty()) {
            $msg = "Tidak ada kandidat jadwal QCO yang mendekati H-3 hari kerja.";
            $this->info($msg);
            Log::info("QCO Reminder: " . $msg);
            return;
        }

        $masterLists = qco___preparation_list::with('department')->get();

        $reminderMessages = [];
        $reminderMessages[] = "⚠️ *PENGINGAT PERSIAPAN QCO (H-3 HARI KERJA)* ⚠️";
        $reminderMessages[] = "Tanggal Hari Ini: " . $today->translatedFormat('d F Y');
        $reminderMessages[] = "----------------------------------------";

        $totalPendingFound = 0;

        foreach ($qcoList as $qco) {
            $coDate = Carbon::parse($qco->change_over_date)->startOfDay();

            // Dapatkan 3 tanggal kirim alert (H-3, H-2, H-1 hari kerja) sebelum change_over_date
            // Array key = urutan hari kerja ke depan (1 = H-1 workday, 2 = H-2 workday, 3 = H-3 workday)
            $alertWorkdays = $this->getWorkdayAlertDates($coDate, 3, $holidays);

            $todayStr = $today->toDateString();
            $matchedWorkdayOffset = null;

            foreach ($alertWorkdays as $workdayOffset => $alertDateStr) {
                if ($alertDateStr === $todayStr) {
                    $matchedWorkdayOffset = $workdayOffset;
                    break;
                }
            }

            // Jika hari ini BUKAN salah satu dari H-3, H-2, atau H-1 hari kerja dari QCO ini, lewati
            if ($matchedWorkdayOffset === null) {
                continue;
            }

            $cellName = $qco->cell_info->area ?? 'Cell ' . $qco->cell;
            $modelName = $qco->model;
            $statusLabel = "H-{$matchedWorkdayOffset} Hari Kerja";

            $uncompletedDepts = [];

            foreach ($masterLists as $master) {
                $matchedPrep = $qco->preparations->firstWhere('id_list', $master->id);

                if (!$matchedPrep) {
                    $deptName = $master->department->nama ?? 'DEPT';
                    $uncompletedDepts[] = "- *{$deptName}*";
                }
            }

            $uniqueUncompletedItems = array_unique($uncompletedDepts);

            if (!empty($uniqueUncompletedItems)) {
                $totalPendingFound++;
                $reminderMessages[] = "";
                $reminderMessages[] = "📌 *Model: {$modelName} ({$cellName})*";
                $reminderMessages[] = "📅 *Jadwal QCO:* {$coDate->translatedFormat('d M Y')} ({$statusLabel})";
                $reminderMessages[] = "Status QCO: {$qco->status} | Stage: {$qco->stage}";
                $reminderMessages[] = "Description: {$qco->description} | Stage: {$qco->stage}";
                $reminderMessages[] = "Belum Melakukan Checklist Persiapan:";
                $reminderMessages = array_merge($reminderMessages, $uniqueUncompletedItems);
            }
        }
        return $reminderMessages;
    }
    protected function isNonWorkingDay(Carbon $date, array $holidays = []): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        $dateStr = $date->toDateString();

        if (!empty($holidays)) {
            return isset($holidays[$dateStr]);
        }

        return Holiday::where('holiday_date', $dateStr)->exists();
    }
    protected function getWorkdayAlertDates(Carbon $targetDate, int $days, array $holidays): array
    {
        $cursor = $targetDate->copy()->startOfDay();
        $alertDates = [];
        $foundWorkdays = 0;
        $guard = 0;

        while ($foundWorkdays < $days && $guard < 60) {
            $cursor->subDay();
            $guard++;

            if ($this->isNonWorkingDay($cursor, $holidays)) {
                continue;
            }

            $foundWorkdays++;
            $alertDates[$foundWorkdays] = $cursor->toDateString();
        }

        return $alertDates;
    }
}
