<?php

namespace App\Services;

use App\Models\qco;
use App\Models\qco___status;
use App\Models\qco___preparation;
use App\Models\qco___preparation_files;
use App\Models\qco___preparation_list;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QcoService {
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService) {
        $this->whatsappService = $whatsappService;
    }

    public function createQco(array $data)
    {
        $noPreparation = filter_var($data['no_preparation'] ?? false, FILTER_VALIDATE_BOOLEAN);
        unset($data['no_preparation']);

        // Cek data eksis (sesuaikan jika perlu per cell/area)
        $exist_qco = qco::where('change_over_date', $data['change_over_date'])
            ->where('model', $data['model'])
            ->exists();

        if ($exist_qco) {
            throw new \Exception('Data tersebut sudah ada di hari yang sama!');
        }

        $qco = null;

        DB::transaction(function () use ($data, $noPreparation, &$qco) {
            $qco = qco::create($data);

            qco___status::firstOrCreate([
                'id_qco' => $qco->id,
            ]);

            $qco->load('cell_info');

            if (!empty($data['parent_id'])) {
                $get_list = qco___preparation::with('preparation_file')
                    ->where('id_qco', $data['parent_id'])
                    ->get();

                foreach ($get_list as $a) {
                    $preparation = qco___preparation::create([
                        'id_qco'  => $qco->id,
                        'id_list' => $a->id_list,
                    ]);

                    foreach ($a->preparation_file as $b) {
                        qco___preparation_files::create([
                            'preparation_id' => $preparation->id,
                            'file'           => $b->file,
                        ]);
                    }
                }
            }else{
                if ($noPreparation == true) {
                    //update completion dan completion_date
                    qco::where('id',$qco->id)->update(['completion'=>100,'completion_date'=>date('Y-m-d H:i:s')]);
                    $kecuali_departement = $data['stage'] == "Mass Production"?"DEVELOPMENT":"PE";
                    $list = qco___preparation_list::with('department')
                            ->whereDoesntHave('department', function ($query) use ($kecuali_departement) {
                                $query->where('nama', $kecuali_departement);
                            })
                            ->get();
                    foreach ($list as $key => $a) {
                        $preparation = qco___preparation::create(['id_qco'=>$qco->id,'id_list'=>$a->id]);
                    }

                }
            }
        });

        // Format tanggal & keterangan waktu
        $coDate = Carbon::parse($qco->change_over_date)->translatedFormat('d M Y');
        $today = Carbon::today();
        $targetDate = Carbon::parse($qco->change_over_date)->startOfDay();

        $ketTanggal = "pada tanggal {$coDate}";
        if ($targetDate->equalTo($today)) {
            $ketTanggal = "HARI INI ({$coDate})";
        } elseif ($targetDate->equalTo($today->copy()->addDay())) {
            $ketTanggal = "BESOK ({$coDate})";
        }

        // Info perubahan jika Update / Reschedule
        $text_update = '';
        if (($data['option'] ?? null) === 'Update') {
            $model_lama = $data['model_lama'] ?? '-';
            $tglLamaFormatted = !empty($data['tanggal_lama'])
                ? Carbon::parse($data['tanggal_lama'])->translatedFormat('d M Y')
                : '-';

            $text_update = "\n🔄 *Perubahan dari Model:* {$model_lama} (Semula tgl: {$tglLamaFormatted})";
        }
        $status_plan = $data['status_plan'] ?? '';
        $text_status = Str::upper((string) $status_plan);

        if ($status_plan === 'rescheduled') {
            $tanggalKapital = Str::upper((string) ($ketTanggal ?? ''));
            $headerMessage = "⚠️ *JADWAL QCO DI-RESCHEDULE UNTUK " . $tanggalKapital . "* ⚠️";
        } else {
            $suffix = $text_status !== '' ? " (" . $text_status . ")" : "";
            $headerMessage = "📢 *JADWAL ".$text_status." CHANGE OVER BARU DIBUAT" . $suffix . "* 📢";
        }

        $area = $qco->cell_info?->area ?? '-';
        $waktuInfo = $targetDate->equalTo($today) || $targetDate->equalTo($today->copy()->addDay())
            ? " ({$ketTanggal})"
            : '';
        $prepNote = $noPreparation
                    ? "*Tidak perlu ada preparation apapun*"
                    : "*Mohon segera lakukan persiapan checklist yang dibutuhkan.*";

        $message = $headerMessage . "\n\n" .
            "*Model:* {$qco->model}\n" .
            "*Cell:* {$area}\n" .
            "*Tanggal CO:* {$coDate}{$waktuInfo}\n" .
            "*Stage:* {$qco->stage}\n" .
            "*Description:* {$qco->description}\n" .
            "{$prepNote} Terima kasih! 🙏" .
            $text_update;

        try {
            $this->whatsappService->sendGroupMessage($message);
        } catch (\Throwable $e) {
            report($e);
        }

        return $message;
    }
    public function messageCancel(array $data) {
        // Format tanggal jika ingin rapi seperti contoh sebelumnya (opsional)
        // $coDate = Carbon::parse($data['change_over_date'])->translatedFormat('d M Y');

        // Karena berbentuk array, gunakan akses kunci ['key']
        $message = "❌ *JADWAL QCO DIBATALKAN* ❌\n\n" .
                "*Model:* " . ($data['model'] ?? '-') . "\n" .
                "*Cell:* " . ($data['cell_info']['area'] ?? '-') . "\n" .
                "*Tanggal CO:* " . ($data['change_over_date'] ?? '-') . "\n" .
                "*Stage:* " . ($data['stage'] ?? '-') . "\n" .
                "Mohon di perhatikan! 🙏";

        // Kirim pesan menggunakan service WhatsApp yang ada
        $this->whatsappService->sendGroupMessage($message);

        // Kembalikan data jika diperlukan, atau return true
        return true;
    }
}
