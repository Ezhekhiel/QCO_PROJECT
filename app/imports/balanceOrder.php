<?php

namespace App\Imports;

use App\Models\balanceOrder as BalanceOrderModel;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class balanceOrder implements
    ToCollection,
    WithChunkReading,
    WithCalculatedFormulas,
    ShouldQueue
{
    public function collection(Collection $collection)
    {
        $arr = [];
        $ke = 0;

        foreach ($collection as $row) {

            // skip header
            if ($ke <= 10) {
                $ke++;
                continue;
            }

            // skip kosong
            if (empty($row[6]) || $row[6] == '-') {
                $ke++;
                continue;
            }

            // buymonth
            if (is_string($row[6]) && str_contains($row[6], '->')) {
                $buymonth = substr($row[6], strpos($row[6], '->') + 2);
            } else {
                $buymonth = $row[6];
            }

            // no urut
            $no_urut = empty($row[47])
                ? '-'
                : str_pad((string)$row[47], 2, '0', STR_PAD_LEFT);

            $arr[] = [

                'buymonth' => $buymonth,

                'batch_date' => $this->formatDate($row[38] ?? null),

                'old_cell' => $row[1] ?? '-',

                'cell' => $row[2] ?? '-',

                'no_urut' => $no_urut,

                'factory' => 'PWN',

                'remark_issue' => $row[5] ?? '-',

                'style_basic' => $row[172] ?? '-',

                'style' => $row[7] ?? '-',

                'category' => $row[171] ?? '-',

                'po' => $row[9] ?? '-',

                'article' => $row[8] ?? '-',

                'wide' => $row[13] ?? '-',

                'customer' => $row[15] ?? '-',

                'market' => $row[14] ?? '-',

                'qty' => $row[46] ?? 0,

                'callot_no' => $row[17] ?? '-',

                'rta' => $this->formatDate($row[18] ?? null),

                'customer_order_no' => $row[11] ?? '-',

                'current_customer_target_xfd' => $this->formatDate($row[40] ?? null),

                'orig_req_xfd_crd_tt' => $this->formatDate($row[41] ?? null),

                'xfd' => $this->formatDate($row[42] ?? null),

                'g' => $row[127] ?? 0,

                'created_at' => now(),

                'updated_at' => now(),
            ];

            $ke++;
        }

        // batch insert
        foreach (array_chunk($arr, 50) as $chunk) {

            try {

                BalanceOrderModel::insert($chunk);

            } catch (\Throwable $e) {

                dd(
                    $e->getMessage(),
                    $e->getLine(),
                    $e->getFile()
                );

            }
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }

    private function formatDate($value)
    {
        try {

            if (is_numeric($value)) {

                return Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');

            }

            return $value ?: '2023-01-01';

        } catch (\Throwable $e) {

            return '2023-01-01';

        }
    }
}
