<?php

namespace App\Http\Controllers;

use App\Models\qco;
use App\Models\cell_info;
use App\Models\qco___status;
use App\Models\qco___preparation;
use App\Models\qco___preparation_files;
use App\Models\qco___preparation_list;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\QcoService;

class QcoController extends Controller
{
    public function form(Request $request)
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

        $qcoList = qco::with(['user', 'user.department', 'user.role','preparations'])
                    ->whereBetween('change_over_date', [$start, $end])
                    ->whereHas('user.department', function ($query) {
                        // Ganti 'PLANNER' dengan nama department yang ingin dicari
                        $query->where('nama', 'PLANNER');
                    })
                    // Tambahkan kondisi ini untuk memfilter cell secara dinamis
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

        $list = qco___preparation_list::where('department_id',$user->department_id)->get();

       // 1. Asumsikan variabel $list berisi master data seluruh checklist (misal: List::all())
        $totalMasterList = $list->count();

        // 2. Tambahkan perhitungan persentase ke setiap objek di dalam $qcoList
        $qcoList = $qcoList->map(function ($qco) use ($list, $totalMasterList) {
            $selectedIds = $qco->preparations->pluck('id_list')->unique();
            $totalChecked = $list->filter(function ($item) use ($selectedIds) {
                return $selectedIds->contains($item->id);
            })->count();
            $percentage = $totalMasterList > 0 ? ($totalChecked / $totalMasterList) * 100 : 0;
            $qco->percent_checklist = round($percentage, 2);
            $qco->list_checklist = $list->map(function ($item) use ($selectedIds) {
                $item->is_checked = $selectedIds->contains($item->id);
                return $item;
            });

            return $qco;
        });
        $cell = cell_info::get();
        return view('qco.form', compact('qcoList', 'currentMonth','cell','list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request, QcoService $qcoService) {
        $request->validate([
            'nik' => 'required',
            'change_over_date' => 'required|date',
            'model' => 'required|string',
            'cell' => 'required|integer',
            'stage' => 'required|string',
        ]);

        try {
            $qcoService->createQco($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Data QCO berhasil disimpan dan notifikasi terkirim!'
            ]);
        } catch (\Exception $e) {
            // Ubah dari return back() menjadi JSON response untuk Ajax
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function store_preparation(Request $request)
    {
        $request->validate([
            'id_qco' => 'required',
            'items' => 'required|array',
            'items.*.files.*' => 'nullable|mimes:jpg,jpeg,png,pdf,xls,xlsx|max:5120',
        ]);

        $idQco = $request->id_qco;

        // Gunakan Database Transaction untuk mengamankan proses
        DB::beginTransaction();

        try {
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    if (isset($item['id_list'])) {
                        $idList = $item['id_list'];

                        // 1. Simpan ke tabel qco___preparation
                        $preparation = qco___preparation::create([
                            'id_qco' => $idQco,
                            'id_list' => $idList,
                        ]);

                        // Validasi apakah baris preparation berhasil dibuat
                        if (!$preparation) {
                            throw new \Exception('Gagal menyimpan data preparation untuk ID list: ' . $idList);
                        }

                        // 2. Cek apakah ada file yang di-upload
                        if (isset($item['files']) && is_array($item['files'])) {
                            foreach ($item['files'] as $file) {
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                                // Pindahkan file fisik
                                if (!$file->move(public_path('data_qco'), $filename)) {
                                    throw new \Exception('Gagal mengunggah file fisik ke server.');
                                }

                                // Simpan ke tabel file
                                $fileRecord = qco___preparation_files::create([
                                    'preparation_id' => $preparation->id,
                                    'file' => $filename
                                ]);

                                if (!$fileRecord) {
                                    throw new \Exception('Gagal mencatat file ke database.');
                                }
                            }
                        }
                    }
                }
            }

            $stage = qco::where('id', $idQco)->value('stage');
            if (!$stage) {
                return response()->json([
                    'success' => false,
                    'message' => 'QCO tidak terdaftar!'
                ], 500);
            }
            // 2. Tentukan kondisi pengecualian
            $whereNot = ($stage === 'Mass Production') ? 'DEVELOPMENT' : 'PE';

            // 3. Query utama menggunakan Laravel 12 syntax
            $count_list = qco___preparation_list::whereRelation('department', 'nama', '!=', $whereNot)->count();
            // 3. Hitung persentase berdasarkan data real di database
            $jumlah_preparation = qco___preparation::with('qco')->where('id_qco', $idQco)->count();
            $jumlah_completion = ($count_list > 0) ? ($jumlah_preparation / $count_list) * 100 : 0;
            $jumlah_completion = round($jumlah_completion, 2);

            $updateData = [
                'completion' => $jumlah_completion,
            ];

            if ($jumlah_completion >= 100) {
                $updateData['status'] = 'DONE';
                $updateData['completion_date'] = date('Y-m-d H:i:s');
            }

            // 4. Eksekusi Update ke tabel qco dan validasi apakah barisnya benar-benar ter-update
            $affectedRows = qco::where('id', $idQco)->update($updateData);

            // Jika id_qco tidak ditemukan di database, update akan menghasilkan 0 baris yang berubah
            if ($affectedRows === 0 && $jumlah_preparation > 0) {
                throw new \Exception('Data QCO utama tidak ditemukan atau tidak ada perubahan data.');
            }

            // Jika semua proses di atas sukses tanpa error, simpan permanen ke database
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data preparation berhasil disimpan dan status diperbarui!'
            ]);

        } catch (\Exception $e) {
            // Jika ada error di baris manapun, batalkan semua perubahan database (Rollback)
            DB::rollBack();

            // Optional: Catat error ke file log Laravel untuk debugging Anda
            Log::error('Error store_preparation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(qco $qco)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(qco $qco)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, qco $qco)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id_qco, $id_list)
    {
        // 1. Hapus data preparation
        $deleted = qco___preparation::where([
            'id_qco' => $id_qco,
            'id_list' => $id_list
        ])->delete();

        // 2. Hitung ulang persentase
        $count_list = qco___preparation_list::count();
        $jumlah_preparation = qco___preparation::where('id_qco', $id_qco)->count();
        $jumlah_completion = ($count_list > 0) ? ($jumlah_preparation / $count_list) * 100 : 0;
        $jumlah_completion = round($jumlah_completion, 2);

        $updateData = [
            'completion' => $jumlah_completion,
        ];

        // 3. Logika Status berdasarkan persentase terbaru
        if ($jumlah_completion >= 100) {
            $updateData['status'] = 'Done';
            $updateData['completion_date'] = date('Y-m-d');
            // Karena di-delete dan kurang dari 100%, kembalikan ke status belum selesai
            $updateData['status'] = 'NOT YET'; // Sesuaikan jika Anda pakai string kosong ""
            $updateData['completion_date'] = null; // Tanggal selesai dihapus/kosongkan
        }

        // 4. Update ke tabel qco
        $affectedRows = qco::where('id', $id_qco)->update($updateData);

        if ($affectedRows === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data QCO utama tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Preparation berhasil dihapus dan persentase diperbarui',
            'completion' => $jumlah_completion,
        ]);
    }
}
