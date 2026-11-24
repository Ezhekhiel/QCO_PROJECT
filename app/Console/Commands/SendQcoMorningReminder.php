<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\qco;
use App\Models\qco___preparation_list;
use App\Models\Holiday;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendQcoMorningReminder extends Command
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    protected $signature = 'qco:send-morning-reminder';
    protected $description = 'Kirim pengingat WhatsApp untuk QCO preparation pada H-3 s/d H-1 hari kerja (mengabaikan weekend & hari libur)';

    public function handle()
    {
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
                $reminderMessages[] = "Description: {$qco->description}";
                $reminderMessages[] = "Belum Melakukan Checklist Persiapan:";
                $reminderMessages = array_merge($reminderMessages, $uniqueUncompletedItems);
            }
        }

        if ($totalPendingFound === 0) {
            $this->info("Tidak ada QCO yang masuk jadwal alert hari ini atau semua persiapan sudah lengkap.");
            return;
        }

        $reminderMessages[] = "";
        $reminderMessages[] = "Mohon segera diselesaikan checklist persiapannya. Terima kasih! 🙏";

        $finalMessage = implode("\n", $reminderMessages);

        try {
            $this->whatsappService->sendGroupMessage($finalMessage);
            $this->info("Peringatan QCO berhasil dikirim ke WhatsApp!");
            Log::info("QCO Reminder: Berhasil dikirim ke group WhatsApp.");
        } catch (\Exception $e) {
            $this->error("Gagal mengirim WA: " . $e->getMessage());
            Log::error("QCO Reminder Error: " . $e->getMessage());
        }
    }

    /**
     * Mengecek apakah tanggal tertentu adalah weekend atau hari libur di tabel Holiday.
     */
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

    /**
     * Mengembalikan array tanggal kirim alert mundur sebanyak $days hari kerja dari $targetDate.
     * Contoh return: [1 => '2026-11-25', 2 => '2026-11-24', 3 => '2026-11-23']
     */
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
