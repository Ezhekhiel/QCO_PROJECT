<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncHolidayCommand extends Command
{
    protected $signature = 'holiday:sync';

    protected $description = 'Sync national holidays';

    public function handle(): int
    {
        $year = now()->year;

        $response = Http::timeout(10)
            ->retry(2, 500)
            ->get('https://api-harilibur.pages.dev/api', [
                'year' => $year,
            ]);

        if (! $response->successful()) {
            $this->error("API gagal (HTTP {$response->status()})");

            return self::FAILURE;
        }

        $holidays = $response->json();

        if (! is_array($holidays)) {
            $this->error('Format respons API tidak valid.');

            return self::FAILURE;
        }

        $synced = 0;

        foreach ($holidays as $holiday) {
            // Filter hanya libur nasional (mengabaikan libur daerah seperti Bali)
            if (empty($holiday['is_national_holiday'])) {
                continue;
            }

            Holiday::updateOrCreate(
                ['holiday_date' => $holiday['holiday_date']],
                ['description' => $holiday['holiday_name']]
            );

            $synced++;
        }

        $this->info("Holiday synced ({$synced} data untuk tahun {$year}).");

        return self::SUCCESS;
    }
}
