<?php

namespace App\Console\Commands;
use App\Models\Holiday;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncHolidayCommand extends Command
{
    protected $signature = 'holiday:sync';

    protected $description = 'Sync national holidays';

    public function handle()
    {
        $year = now()->year;

        $response = Http::get(
            "https://libur.deno.dev/api?year={$year}"
        );

        if (!$response->successful()) {

            $this->error('API gagal');

            return;
        }

        $holidays = $response->json();

        foreach ($response->json() as $holiday) {

            Holiday::updateOrCreate(
                [
                    'holiday_date' => $holiday['date']
                ],
                [
                    'description' => $holiday['name']
                ]
            );
        }

        $this->info('Holiday synced');
    }
}
