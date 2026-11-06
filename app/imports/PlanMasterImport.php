<?php

namespace App\Imports;

use App\Models\PlanMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PlanMasterImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        $cell      = isset($row[0]) ? trim((string)$row[0]) : null;
        $model     = isset($row[1]) ? trim((string)$row[1]) : null;
        $style     = isset($row[2]) ? trim((string)$row[2]) : null;
        $gen       = $row[3] ?? 0;
        $po       = $row[4] ?? 0;
        $qty       = $row[5] ?? 0;
        $startAssy = $row[6] ?? null;
        $startStit = $row[7] ?? null; // Ambil kolom stitching asli jika ada

        // 1. Skip baris pemisah header cell
        if (!empty($style) && stripos($style, 'CELL B') !== false) {
            return null;
        }

        // 2. Skip baris kosong atau header judul
        if (empty($cell) || empty($style) || strtolower($style) === 'style') {
            return null;
        }

        // Parse Tanggal Assembly terlebih dahulu
        $assemblyDate = $this->transformDate($startAssy);

        // Parse Tanggal Stitching, jika kosong hitung mundur 2 hari kerja (Senin-Jumat) dari Assembly
        $stitchingDate = $this->transformDate($startStit);
        if (!$stitchingDate && $assemblyDate) {
            // Mundur 2 hari kerja (otomatis melewati Sabtu & Minggu)
            $stitchingDate = Carbon::parse($assemblyDate)->subWeekdays(2)->format('Y-m-d');
        }

        return new PlanMaster([
            'cell'                 => $cell,
            'model'                => $model,
            'style_no'             => $style,
            'gen'                  => $gen,
            'po'                   => $po,
            'qty'                  => is_numeric($qty) ? (int)$qty : 0,
            'assembly_start_date'  => $assemblyDate,
            'stitching_start_date' => $stitchingDate,
        ]);
    }

    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
