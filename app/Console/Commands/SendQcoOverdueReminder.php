<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\qco;
use App\Models\Holiday;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendQcoOverdueReminder extends Command
{
    protected $signature = 'qco:send-overdue-reminder';

    protected $description = 'Reminder QCO yang sudah lewat jadwal namun Actual Change Over belum diinput';

    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        parent::__construct();

        $this->whatsappService = $whatsappService;
    }

    public function handle()
    {
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

    protected function isNonWorkingDay(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        return Holiday::where(
            'holiday_date',
            $date->toDateString()
        )->exists();
    }
}
