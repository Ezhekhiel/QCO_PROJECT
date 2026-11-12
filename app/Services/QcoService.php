<?php

namespace App\Services;

use App\Models\qco;
use App\Models\qco___status;
use Carbon\Carbon;

class QcoService {
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService) {
        $this->whatsappService = $whatsappService;
    }

    public function createQco(array $data) {
        // Cek data eksis
        $exist_qco = qco::where([
            'change_over_date' => $data['change_over_date'],
            'model' => $data['model']
        ])->exists();

        if ($exist_qco) {
            throw new \Exception('Data tersebut sudah ada!');
        }

        $qco = qco::create($data);

        qco___status::firstOrCreate([
            'id_qco' => $qco->id
        ]);
        $qco->load('cell_info');

        // Format Tanggal Baru
        $coDate = Carbon::parse($qco->change_over_date)->translatedFormat('d M Y');

        // Cek apakah tanggal QCO adalah Hari Ini atau Besok
        $today = Carbon::today();
        $targetDate = Carbon::parse($qco->change_over_date)->startOfDay();

        $ketTanggal = "pada tanggal {$coDate}";
        if ($targetDate->equalTo($today)) {
            $ketTanggal = "HARI INI ({$coDate})";
        } elseif ($targetDate->equalTo($today->copy()->addDay())) {
            $ketTanggal = "BESOK ({$coDate})";
        }

        // Penambahan info model & tanggal lama jika ini adalah update/reschedule
        $text_update = '';
        if (isset($data['option']) && $data['option'] == "Update") {
            $model_lama = $data['model_lama'] ?? '-';

            // Format Tanggal Lama
            $tglLamaFormatted = '-';
            if (isset($data['tanggal_lama'])) {
                $tglLamaFormatted = Carbon::parse($data['tanggal_lama'])->translatedFormat('d M Y');
            }

            $text_update = "\n🔄 *Perubahan dari Model:* {$model_lama} (Semula tgl: {$tglLamaFormatted})";
        }

        // Header pesan dinamis
        $headerMessage = "📢 *JADWAL QCO BARU DIBUAT* 📢";
        if (isset($data['status_plan']) && $data['status_plan'] == 'rescheduled') {
            $headerMessage = "⚠️ *JADWAL QCO DI-RESCHEDULE UNTUK " . strtoupper($ketTanggal) . "* ⚠️";
        }

        $message = $headerMessage . "\n\n" .
                "*Model Baru:* {$qco->model}\n" .
                "*Cell:* {$qco->cell_info->area}\n" .
                "*Tanggal CO:* {$coDate}\n" .
                "*Stage:* {$qco->stage}\n" .
                "Mohon segera lakukan persiapan checklist yang dibutuhkan. Terima kasih! 🙏\n" .
                $text_update;

        $this->whatsappService->sendGroupMessage($message);

        return $qco;
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
