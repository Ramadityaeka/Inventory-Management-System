<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StockOverviewExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithStrictNullComparison
{
    protected $stocks;

    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }

    public function collection()
    {
        return $this->stocks;
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item Name',
            'Category',
            'Warehouse',
            'Quantity',
            'Unit',
            'Harga Terakhir',
            'Status'
        ];
    }

    public function map($stock): array
    {
        // Get latest approved submission for unit price
        $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
            ->where('warehouse_id', $stock->warehouse_id)
            ->where('status', 'approved')
            ->whereNotNull('unit_price')
            ->orderBy('submitted_at', 'desc')
            ->first();

        $unitPrice = $latestSubmission ? (float) $latestSubmission->unit_price : 0;

        return [
            $stock->item->code ?? '-',
            $stock->item->name ?? '-',
            $stock->item->category->name ?? '-',
            $stock->warehouse->name ?? '-',
            (int) ($stock->quantity ?? 0),
            $stock->item->unit ?? '-',
            $unitPrice > 0 ? number_format($unitPrice, 0, ',', '.') : '-',
            $this->getStatus($stock)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style data rows
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }

    private function getStatus($stock)
    {
        if ($stock->quantity == 0) {
            return 'Habis';
        } else {
            return 'Tersedia';
        }
    }
}