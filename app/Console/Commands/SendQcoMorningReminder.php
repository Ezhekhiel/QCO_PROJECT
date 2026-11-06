<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\qco;
use App\Models\qco___preparation_list;
use App\Models\WhatsappGroup;
use App\Models\WhatsappLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendQcoMorningReminder extends Command
{
    protected $signature = 'qco:send-morning-reminder';
    protected $description = 'Kirim pengingat WhatsApp untuk QCO preparation H-3, H-2, dan H-1 yang belum lengkap';

    public function handle()
    {
        $today = Carbon::today();
        $todayString = $today->toDateString(); // Format: YYYY-MM-DD

        // Tentukan rentang H-1 sampai H-3 dari hari ini
        $targetStart = $today->copy()->addDays(1)->format('Y-m-d'); // Besok (H-1)
        $targetEnd = $today->copy()->addDays(3)->format('Y-m-d');   // 3 hari ke depan (H-3)

        // 1. Ambil data QCO dalam rentang H-3 s.d H-1 atau yang masih NOT YET
        $qcoList = qco::with(['preparations', 'cell_info'])
            ->where(function ($query) use ($targetStart, $targetEnd, $todayString) {
                $query->whereBetween('change_over_date', [$targetStart, $targetEnd])
                    ->orWhere(function ($q) use ($todayString) {
                        $q->where('change_over_date', '<', $todayString)
                            ->where('status', 'NOT YET');
                    });
            })
            ->orderBy('change_over_date', 'asc')
            ->get();

        if ($qcoList->isEmpty()) {
            $msg = "Tidak ada jadwal Change Over (QCO) untuk rentang H-1 sampai H-3 ke depan.";
            $this->info($msg);
            Log::info("QCO Reminder: " . $msg);
            return;
        }

        // 2. Ambil master preparation list beserta departemennya
        $masterLists = qco___preparation_list::with('department')->get();

        $reminderMessages = [];
        $reminderMessages[] = "⚠️ *PENGINGAT PERSIAPAN QCO (H-3 s.d H-1)* ⚠️";
        $reminderMessages[] = "Tanggal Hari Ini: " . Carbon::today()->translatedFormat('d F Y');
        $reminderMessages[] = "----------------------------------------";

        $totalPendingFound = 0;

        foreach ($qcoList as $qco) {
            $cellName = $qco->cell_info->area ?? 'Cell ' . $qco->cell;
            $modelName = $qco->model;

            // Hitung sisa hari menuju Change Over
            $coDate = Carbon::parse($qco->change_over_date);
            $diffDays = $today->diffInDays($coDate, false);

            // [PERBAIKAN] Inisialisasi ulang array uncompletedDepts untuk setiap QCO baru
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
                // [PERBAIKAN] Gabungkan string model & cell agar tidak error enter
                $reminderMessages[] = "📌 *Model: {$modelName} ({$cellName})*";
                $reminderMessages[] = "📅 *Jadwal QCO:* {$coDate->translatedFormat('d M Y')} (H-{$diffDays})";
                $reminderMessages[] = "Status QCO: {$qco->status} | Stage: {$qco->stage}";
                $reminderMessages[] = "Belum Melakukan Checklist Persiapan:";
                $reminderMessages = array_merge($reminderMessages, $uniqueUncompletedItems);
            }
        }

        if ($totalPendingFound === 0) {
            $this->info("Semua persiapan QCO untuk rentang H-3 s.d H-1 sudah lengkap.");
            return;
        }

        $reminderMessages[] = "";
        $reminderMessages[] = "Mohon segera diselesaikan checklist persiapannya. Terima kasih! 🙏";

        $finalMessage = implode("\n", $reminderMessages);

        // 3. Ambil target grup WhatsApp dari database
        $targetGroup = WhatsappGroup::where('code_group', '120363431380455863@g.us')->first() ?? WhatsappGroup::first();

        if (!$targetGroup) {
            $this->error("Gagal mengirim WA: Belum ada data Grup WhatsApp yang tersimpan di database.");
            return;
        }

        $waTargetGroup = $targetGroup->code_group;
        $gatewayUrl = env('WA_GATEWAY_URL', 'http://localhost:50521');

        try {
            $response = Http::withHeaders([
                'x-api-key' => env('WA_GATEWAY_KEY', 'my-secret-key')
            ])->post($gatewayUrl . '/send-text', [
                'target' => $waTargetGroup,
                'message' => $finalMessage
            ]);

            if ($response->successful()) {
                $this->info("Peringatan QCO berhasil dikirim ke WhatsApp!");
                Log::info("QCO Reminder: Berhasil dikirim ke group {$waTargetGroup}");

                WhatsappLog::create([
                    'target_group' => $waTargetGroup,
                    'message' => $finalMessage,
                    'message_id' => $response->json('message_id') ?? null,
                    'status' => 'sent'
                ]);
            } else {
                $errorMsg = "Gagal mengirim WA: " . $response->body();
                $this->error($errorMsg);
                Log::error("QCO Reminder Error: " . $errorMsg);

                WhatsappLog::create([
                    'target_group' => $waTargetGroup,
                    'message' => $finalMessage,
                    'status' => 'failed'
                ]);
            }
        } catch (\Exception $e) {
            $this->error("Error Koneksi ke WA Gateway: " . $e->getMessage());
        }
    }
}
