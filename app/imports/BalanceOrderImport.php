<?php

namespace App\Imports;

use App\Models\balanceOrder;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BalanceOrderImport implements
    ToModel,
    WithChunkReading,
    WithBatchInserts,
    WithCalculatedFormulas,
    WithStartRow,
    ShouldQueue
{
    public function startRow(): int
    {
        return 12;
    }

    public function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {

            if (is_numeric($value)) {

                return Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            return Carbon::parse($value)
                ->format('Y-m-d');

        } catch (\Exception $e) {

            return null;

        }
    }

    public function model(array $row)
    {
        // dd($row);
        if (empty($row[6])) {
            return null;
        }
        /*
        |--------------------------------------------------------------------------
        | PROGRESS
        |--------------------------------------------------------------------------
        */

        $processed = Cache::increment('import_processed_rows');

        $totalRows = Cache::get('import_total_rows', 1);

        Cache::put(
            'import_progress',
            intval(($processed / $totalRows) * 100)
        );
        if (!$row[47]) {
            $no_urut = '-';
        }else{
            if (strlen($row[47])==1) {
                $no_urut = '0'.$row[47];
            }else{
                $no_urut = $row[47];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        return new balanceOrder([

            'buymonth' => $row[6] ?? null,

            'old_cell' => $row[1] ?? '-',

            'cell' => $row[2] ?? null,

            'style' => $row[7] ?? null,

            'no_urut'=>$no_urut,

            'remark_issue'=>$row[5]??"-",

            'customer_order_no'=>$row[11]??'-',

            'article' => $row[8] ?? null,

            'category'=>$row[172]??"-",

            'style_basic'=>$row[173]??"-",

            'factory'=>'PWN',

            'po' => $row[9] ?? null,

            'wide' => $row[13] ?? null,

            'market' => $row[14] ?? '-',

            'customer' => $row[15] ?? '-',

            'callot_no' => $row[17] ?? "-",

            'rta' => $this->transformDate($row[18] ?? "2023-01-01"),

            'batch_date' => $this->transformDate($row[38] ?? '2023-01-01'),

            'current_customer_target_xfd'
                => $this->transformDate($row[40] ?? "2023-01-01"),

            'orig_req_xfd_crd_tt'
                => $this->transformDate($row[41] ?? "2023-01-01"),

            'xfd'
                => $this->transformDate($row[42] ?? "2023-01-01"),

            'qty' => $row[46] ?? 0,

            'g' => $row[127] ?? null,

            'size_1' => $row[128] ?? 0,
            'size_2' => $row[129] ?? 0,
            'size_3' => $row[130] ?? 0,
            'size_4' => $row[131] ?? 0,
            'size_5' => $row[132] ?? 0,
            'size_6' => $row[133] ?? 0,
            'size_7' => $row[134] ?? 0,
            'size_8' => $row[135] ?? 0,
            'size_9' => $row[136] ?? 0,
            'size_10' => $row[137] ?? 0,
            'size_11' => $row[138] ?? 0,
            'size_12' => $row[139] ?? 0,
            'size_13' => $row[140] ?? 0,
            'size_14' => $row[141] ?? 0,
            'size_15' => $row[142] ?? 0,
            'size_16' => $row[143] ?? 0,
            'size_17' => $row[144] ?? 0,
            'size_18' => $row[145] ?? 0,
            'size_19' => $row[146] ?? 0,
            'size_20' => $row[147] ?? 0,
            'size_21' => $row[148] ?? 0,
            'size_22' => $row[149] ?? 0,
            'size_23' => $row[150] ?? 0,
            'size_24' => $row[151] ?? 0,
            'size_25' => $row[152] ?? 0,
            'size_26' => $row[153] ?? 0,
            'size_27' => $row[154] ?? 0,
            'size_28' => $row[155] ?? 0,
            'size_29' => $row[156] ?? 0,
        ]);
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function batchSize(): int
    {
        return 20;
    }
}
