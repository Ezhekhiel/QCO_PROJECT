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
use Illuminate\Support\Facades\File;

class QcoController extends Controller
{
    public function form(Request $request)
    {
            // Ambil data user yang sedang login
        $user = Auth::user();
        $user->load('department');
        $department = $user->department->nama;
        // Cek manual department_id (misal: hanya departemen dengan ID 1 yang boleh akses)
        $selectedCells = $request->input('cell', []);

        $currentMonth = $request->input('month') ? $request->input('month') . '-01' : Carbon::now()->format('Y-m-d');
        $cell = $request->input('cell') ? $request->input('cell'): "";
        $get_user = User::get();
        // Ambil data qco berdasarkan rentang bulan yang sedang aktif di kalender
        $start = Carbon::parse($currentMonth)->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = Carbon::parse($currentMonth)->endOfMonth()->endOfWeek(Carbon::SATURDAY);

         $qcoList = qco::with(['qco_status','user', 'user.department', 'user.role','preparations','cell_info','preparations.preparation_file','preparations.user'])
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

    public function store(Request $request, QcoService $qcoService)
    {
        $request->validate([
            'nik' => 'required',
            'change_over_date' => 'required|date',
            'model' => 'required|string',
            'cell' => 'required|integer',
            'stage' => 'required|string',
            'description' => 'required|string',
            'no_preparation' => 'nullable|boolean',
        ]);

        $data = array_merge($request->all(), [
            'no_preparation' => $request->boolean('no_preparation'),
        ]);
        $noPreparation = $request->no_preparation;
        if ($noPreparation == true) {
            $kecuali_departement = $data['stage'] == "Mass Production"?"DEVELOPMENT":"PE";
        }
        $list = qco___preparation_list::with('department')->get();
        return $list;

        try {
            // Pakai $data, bukan $request->all()
            $qcoService->createQco($data);

            return response()->json([
                'success' => true,
                'message' => 'Data QCO berhasil disimpan dan notifikasi terkirim!'
            ]);
        } catch (\Exception $e) {
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
            // Ubah 'nullable' menjadi 'required' dan pastikan berupa array dengan minimal 1 file
            'items.*.files' => 'required|array|min:1',
            'items.*.files.*' => 'required|mimes:jpg,jpeg,png,pdf,xls,xlsx|max:5120',
        ], [
            // Custom pesan error agar lebih mudah dibaca (opsional)
            'items.*.files.required' => 'Setiap item yang dipilih wajib melampirkan file.',
            'items.*.files.min' => 'Setiap item yang dipilih minimal harus memiliki 1 file lampiran.',
            'items.*.files.*.mimes' => 'Format file tidak valid (diizinkan: jpg, jpeg, png, pdf, xls, xlsx).',
            'items.*.files.*.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        $idQco = $request->id_qco;

        // Gunakan Database Transaction untuk mengamankan proses
        DB::beginTransaction();

        try {
            $processedIdLists = [];
            $skippedIdLists = [];

            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    if (!isset($item['id_list'])) {
                        continue;
                    }

                    $idList = $item['id_list'];

                    // Lewati jika id_list sudah diproses di request ini atau sudah ada di DB untuk id_qco ini
                    $alreadyInRequest = in_array($idList, $processedIdLists, true);
                    $alreadyInDb = qco___preparation::where('id_qco', $idQco)
                        ->where('id_list', $idList)
                        ->exists();

                    if ($alreadyInRequest || $alreadyInDb) {
                        if (!in_array($idList, $skippedIdLists, true)) {
                            $skippedIdLists[] = $idList;
                        }
                        continue;
                    }

                    $processedIdLists[] = $idList;

                    // 1. Simpan ke tabel qco___preparation
                    $preparation = qco___preparation::create([
                        'id_qco' => $idQco,
                        'id_list' => $idList,
                    ]);

                    if (!$preparation) {
                        throw new \Exception('Gagal menyimpan data preparation untuk ID list: ' . $idList);
                    }

                    // 2. Simpan file hanya untuk item yang baru dibuat
                    if (isset($item['files']) && is_array($item['files'])) {
                        foreach ($item['files'] as $file) {
                            $originalName = basename($file->getClientOriginalName());
                            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                            $extension = $file->getClientOriginalExtension();

                            $filename = $originalName;
                            $counter = 1;

                            while (file_exists(public_path('data_qco/' . $filename))) {
                                $filename = $nameWithoutExt . ' (' . $counter . ').' . $extension;
                                $counter++;
                            }

                            if (!$file->move(public_path('data_qco'), $filename)) {
                                throw new \Exception('Gagal mengunggah file fisik ke server.');
                            }

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

            $stage = qco::where('id', $idQco)->value('stage');
            if (!$stage) {
                throw new \Exception('QCO tidak terdaftar!');
                return response()->json([
                    'success' => false,
                    'message' => 'QCO tidak terdaftar!'
                ], 500);
            }

            $whereNot = ($stage === 'Mass Production') ? 'DEVELOPMENT' : 'PE';

            $count_list = qco___preparation_list::whereRelation('department', 'nama', '!=', $whereNot)->count();
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

            $affectedRows = qco::where('id', $idQco)->update($updateData);

            if ($affectedRows === 0 && $jumlah_preparation > 0) {
                throw new \Exception('Data QCO utama tidak ditemukan atau tidak ada perubahan data.');
            }

            DB::commit();
            $message = 'Data preparation berhasil disimpan dan status diperbarui!';
            if (!empty($skippedIdLists)) {
                $message .= ' Beberapa item/file tidak disimpan karena sudah ada sebelumnya (ID list: ' . implode(', ', $skippedIdLists) . ').';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'skipped_id_lists' => $skippedIdLists,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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

        $preparation = qco___preparation::where([
            'id_qco'  => $id_qco,
            'id_list' => $id_list
        ])->first();

        $preparation_id = $preparation?->id;

        // 2. hapus file dan preparation files
        $preparation_files = qco___preparation_files::where([
            'preparation_id' => $preparation_id,
        ])->get();

        if ($preparation_files->isNotEmpty()) {
            foreach ($preparation_files as $item) {
                if (!empty($item->file)) {
                    $filePath = public_path('data_qco/' . $item->file);
                    if (File::exists($filePath)) {
                        $deleted = File::delete($filePath);

                        if (!$deleted) {
                            return "Gagal menhapus files";
                            Log::warning('Gagal menghapus file: ' . $filePath);
                        }
                    } else {
                            return "File tidak ditemukan di path";
                        Log::warning('File tidak ditemukan di path: ' . $filePath);
                    }
                }

                $item->delete();
            }
        }

        // hapus dulu file tadi di table
        if ($preparation) {
            $preparation->delete();
        }

        // 3. Hitung ulang persentase
        $count_list = qco___preparation_list::count();
        $jumlah_preparation = qco___preparation::where('id_qco', $id_qco)->count();
        $jumlah_completion = ($count_list > 0) ? ($jumlah_preparation / $count_list) * 100 : 0;
        $jumlah_completion = round($jumlah_completion, 2);

        $updateData = [
            'completion' => $jumlah_completion,
        ];

        // 4. Logika Status berdasarkan persentase terbaru
        if ($jumlah_completion >= 100) {
            $updateData['status'] = 'Done';
            $updateData['completion_date'] = date('Y-m-d');
            // Karena di-delete dan kurang dari 100%, kembalikan ke status belum selesai
            $updateData['status'] = 'NOT YET'; // Sesuaikan jika Anda pakai string kosong ""
            $updateData['completion_date'] = null; // Tanggal selesai dihapus/kosongkan
        }

        // 5. Update ke tabel qco
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

    public function viewPreparationFile($id)
    {
        $fileRecord = qco___preparation_files::findOrFail($id);

        $filename = basename($fileRecord->file);
        $path = public_path('data_qco/' . $filename);

        abort_unless(is_file($path), 404, 'File tidak ditemukan.');

        $mimeType = File::mimeType($path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadPreparationFile($id)
    {
        $fileRecord = qco___preparation_files::findOrFail($id);

        $filename = basename($fileRecord->file);
        $path = public_path('data_qco/' . $filename);

        abort_unless(is_file($path), 404, 'File tidak ditemukan.');

        return response()->download($path, $filename);
    }

}
