<?php

namespace App\Http\Controllers\folder_test;

use App\Models\qco;
use App\Models\Holiday;
use App\Services\QcoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\qco___preparation_list;

class testController extends Controller
{

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
    public function testOverDue() {
        $today = Carbon::today();

        // Skip jika weekend atau hari libur
        if ($this->isNonWorkingDay($today)) {

            $msg = "Hari ini libur. Reminder overdue QCO tidak dikirim.";

            $this->info($msg);

            Log::info($msg);

            return;
        }

        $qcoList = qco::with([
                'cell_info',
                'qco_status',
                'preparations'
            ])
            ->where('status', '!=', 'CANCEL')
            // HANYA YANG SUDAH LEWAT
            ->whereDate('change_over_date', '<', $today)
            // actual belum diisi
            ->where(function ($query) {

                $query->whereDoesntHave('qco_status')

                    ->orWhereHas('qco_status', function ($q) {

                        $q->whereNull('actual_change_over')

                          ->orWhere('actual_change_over', '');

                    });

            })

            ->orderBy('change_over_date', 'asc')
            ->get();
        $masterLists = qco___preparation_list::with('department')->get();

        if ($qcoList->isEmpty()) {

            $msg = "Tidak ada QCO overdue yang perlu diingatkan.";
            $this->info($msg);
            Log::info($msg);
            return;
        }

        $messages = [];

        $messages[] = "⚠️ *QCO OVERDUE REMINDER* ⚠️";
        $messages[] = "📅 Tanggal : " . $today->translatedFormat('d F Y');
        $messages[] = "";
        $messages[] = "Terdapat QCO yang telah melewati jadwal namun *Actual Change Over* belum diinput.";
        $messages[] = "";
        $messages[] = "━━━━━━━━━━━━━━━━━━";

        foreach ($qcoList as $qco) {
            $uncompletedDepts = [];
            foreach ($masterLists as $master) {
                $matchedPrep = $qco->preparations
                    ->firstWhere('id_list', $master->id);
                if (!$matchedPrep) {
                    $deptName =
                        $master->department->nama ?? 'DEPT';
                    $uncompletedDepts[] = $deptName;
                }
            }

            $uncompletedDepts = array_unique($uncompletedDepts);

            $coDate = Carbon::parse($qco->change_over_date);

            $lateDays = $coDate->diffInDays($today);

            $cellName = $qco->cell_info->area
                ?? ('Cell ' . $qco->cell);

            $messages[] = "📌 *Model : {$qco->model}*";
            $messages[] = "• 🏭 Cell : {$cellName}";
            $messages[] = "• 📅 Jadwal : " . $coDate->translatedFormat('d M Y');
            $messages[] = "• ⏳ Terlambat : {$lateDays} hari";
            $messages[] = "• 📝 Description : " . ($qco->description ?? '-');
            $messages[] = "• 📊 Status : {$qco->status}";
            if (!empty($uncompletedDepts)) {
                $messages[] =
                    "❌ *Belum Checklist:* "
                    . implode(', ', $uncompletedDepts);
            }
            $messages[] = "━━━━━━━━━━━━━━━━━━";
        }

        $messages[] = "";
        $messages[] = "Mohon kepada Department *PRODUKSI* terkait untuk segera menyelesaikan persiapan dan melakukan update *Actual Change Over*..";
        $messages[] = "Terima kasih 🙏";

        $finalMessage = implode("\n", $messages);
        return $finalMessage;

        try {

            $this->whatsappService->sendGroupMessage($finalMessage);

            $this->info("Reminder overdue berhasil dikirim.");

            Log::info("QCO Overdue Reminder berhasil dikirim.");

        } catch (\Exception $e) {

            $this->error("Gagal mengirim WA : " . $e->getMessage());

            Log::error(
                "QCO Overdue Reminder Error : "
                . $e->getMessage()
            );
        }
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
