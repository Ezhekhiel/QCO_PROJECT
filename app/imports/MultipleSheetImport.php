<?php

namespace App\Imports;

use Illuminate\Support\Facades\Cache;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleSheetImport implements
    WithMultipleSheets,
    WithEvents
{
    public function sheets(): array
    {
        return [

            'NB' => new BalanceOrderImport(),

        ];
    }

    public function registerEvents(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | IMPORT SUCCESS
            |--------------------------------------------------------------------------
            */

            AfterImport::class => function () {

                Cache::put('import_status', 'done');

                Cache::put('import_progress', 100);
            },

            /*
            |--------------------------------------------------------------------------
            | IMPORT FAILED
            |--------------------------------------------------------------------------
            */

            ImportFailed::class => function (ImportFailed $event) {
                Cache::put('import_status', 'failed');

                Cache::put(
                    'import_error',
                    $event->getException()->getMessage()
                );
            },

        ];
    }
}
