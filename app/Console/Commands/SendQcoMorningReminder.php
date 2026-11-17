<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\qco;
use App\Models\qco___preparation_list;
use App\Models\WhatsappGroup;
use App\Models\WhatsappLog;
use App\Models\Holiday;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendQcoMorningReminder extends Command
{
    protected $whatsappService;

    // Dependency Injection Service WhatsApp ke dalam Command
    public function __construct(WhatsappService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    protected $signature = 'qco:send-morning-reminder';
    protected $description = 'Kirim pengingat WhatsApp untuk QCO preparation (Hanya untuk H-3 ke bawah/mendekati Hari H, serta mengabaikan hari libur & weekend)';

    public function handle()
    {
        $today = Carbon::today();
        $todayString = $today->toDateString(); // Format: YYYY-MM-DD

        // Cek apakah hari ini weekend (Sabtu/Minggu) atau Hari Libur Nasional
        $isWeekend = $today->isWeekend();
        $isHoliday = Holiday::where('holiday_date', $todayString)->exists();

        if ($isWeekend || $isHoliday) {
            $liburInfo = $isWeekend ? "Weekend (" . $today->translatedFormat('l') . ")" : "Hari Libur Nasional";
            $msg = "Hari ini adalah {$liburInfo}. Pengingat QCO dibatalkan dan tidak dikirim.";
            $this->info($msg);
            Log::info("QCO Reminder: " . $msg);
            return;
        }

        // Batasi rentang tanggal: Hanya ambil QCO dari hari ini sampai 3 hari ke depan (H-3)
        // plus QCO yang sudah lewat tapi masih NOT YET jika diperlukan,
        // namun untuk murni H-3, kita atur batas tanggal maksimumnya adalah $today + 3 hari.
        $targetMaxDate = $today->copy()->addDays(3)->toDateString();

        // 1. Ambil data QCO dari hari ini sampai H-3 (change_over_date antara hari ini dan 3 hari ke depan)
        $qcoList = qco::with(['preparations', 'cell_info'])
            ->where('status', '!=', 'CANCEL')
            ->whereBetween('change_over_date', [$todayString, $targetMaxDate])
            ->orderBy('change_over_date', 'asc')
            ->get();

        if ($qcoList->isEmpty()) {
            $msg = "Tidak ada jadwal Change Over (QCO) dalam rentang H-3 dari hari ini.";
            $this->info($msg);
            Log::info("QCO Reminder: " . $msg);
            return;
        }

        // 2. Ambil master preparation list beserta departemennya
        $masterLists = qco___preparation_list::with('department')->get();

        $reminderMessages = [];
        $reminderMessages[] = "⚠️ *PENGINGAT PERSIAPAN QCO (H-3)* ⚠️";
        $reminderMessages[] = "Tanggal Hari Ini: " . $today->translatedFormat('d F Y');
        $reminderMessages[] = "----------------------------------------";

        $totalPendingFound = 0;

        foreach ($qcoList as $qco) {
            $cellName = $qco->cell_info->area ?? 'Cell ' . $qco->cell;
            $modelName = $qco->model;

            $coDate = Carbon::parse($qco->change_over_date)->startOfDay();
            $todayDate = $today->copy()->startOfDay();

            // Penentuan label status hari
            if ($coDate->eq($todayDate)) {
                $statusLabel = "Hari H";
            } else {
                $diffDays = abs($todayDate->diffInDays($coDate));
                $statusLabel = "H-{$diffDays}";
            }

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
                $reminderMessages[] = "Belum Melakukan Checklist Persiapan:";
                $reminderMessages = array_merge($reminderMessages, $uniqueUncompletedItems);
            }
        }

        if ($totalPendingFound === 0) {
            $this->info("Semua persiapan QCO untuk rentang H-3 sudah lengkap.");
            return;
        }

        $reminderMessages[] = "";
        $reminderMessages[] = "Mohon segera diselesaikan checklist persiapannya. Terima kasih! 🙏";

        $finalMessage = implode("\n", $reminderMessages);

        // 3. Kirim pesan menggunakan Service yang sudah di-inject
        try {
            $this->whatsappService->sendGroupMessage($finalMessage);
            $this->info("Peringatan QCO berhasil dikirim ke WhatsApp!");
            Log::info("QCO Reminder: Berhasil dikirim ke group WhatsApp.");
        } catch (\Exception $e) {
            $this->error("Gagal mengirim WA: " . $e->getMessage());
            Log::error("QCO Reminder Error: " . $e->getMessage());
        }
    }
}
