<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockValueReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithStrictNullComparison, WithEvents
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->where('quantity', '>', 0);

        // Apply filters
        if (isset($this->filters['category_id']) && !empty($this->filters['category_id'])) {
            $query->whereHas('item', function($q) {
                $q->where('category_id', $this->filters['category_id']);
            });
        }

        if (isset($this->filters['item_name']) && !empty($this->filters['item_name'])) {
            $query->whereHas('item', function($q) {
                $q->where('name', 'LIKE', '%' . $this->filters['item_name'] . '%');
            });
        }

        if (isset($this->filters['item_code']) && !empty($this->filters['item_code'])) {
            $query->whereHas('item', function($q) {
                $q->where('code', 'LIKE', '%' . $this->filters['item_code'] . '%');
            });
        }

        if (isset($this->filters['warehouse_id']) && !empty($this->filters['warehouse_id'])) {
            $query->where('warehouse_id', $this->filters['warehouse_id']);
        }

        // Support for multiple warehouse IDs (for admin gudang)
        if (isset($this->filters['warehouse_ids']) && !empty($this->filters['warehouse_ids'])) {
            $query->whereIn('warehouse_id', $this->filters['warehouse_ids']);
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Gudang',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Jumlah',
            'Satuan',
            'Harga/Satuan (Rp)',
            'Harga Total (Rp)'
        ];
    }

    public function map($stock): array
    {
        static $index = 0;
        $index++;

        // Get latest approved submission for unit price
        $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
            ->where('warehouse_id', $stock->warehouse_id)
            ->where('status', 'approved')
            ->whereNotNull('unit_price')
            ->orderBy('submitted_at', 'desc')
            ->first();

        $unitPrice = $latestSubmission ? (float) $latestSubmission->unit_price : 0;
        $totalValue = $stock->quantity * $unitPrice;

        return [
            $index,
            $stock->warehouse->name ?? '-',
            $stock->item->code ?? '-',
            $stock->item->name ?? '-',
            $stock->item->category->name ?? '-',
            (int) $stock->quantity,
            $stock->item->unit ?? '-',
            $unitPrice,
            $totalValue
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok & Nilai';
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:I1')->applyFromArray([
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
        if ($lastRow > 1) {
            $sheet->getStyle('A2:I' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                // Get all data to calculate totals
                $stocks = $this->collection();
                $totalQuantity = 0;
                $totalValue = 0;
                
                foreach ($stocks as $stock) {
                    $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
                        ->where('warehouse_id', $stock->warehouse_id)
                        ->where('status', 'approved')
                        ->whereNotNull('unit_price')
                        ->orderBy('submitted_at', 'desc')
                        ->first();
                    
                    $unitPrice = $latestSubmission ? (float) $latestSubmission->unit_price : 0;
                    $totalQuantity += $stock->quantity;
                    $totalValue += $stock->quantity * $unitPrice;
                }
                
                // Add empty row
                $totalRow = $lastRow + 1;
                
                // Add TOTAL KESELURUHAN row
                $sheet->setCellValue('A' . $totalRow, '');
                $sheet->setCellValue('B' . $totalRow, '');
                $sheet->setCellValue('C' . $totalRow, '');
                $sheet->setCellValue('D' . $totalRow, '');
                $sheet->setCellValue('E' . $totalRow, 'TOTAL KESELURUHAN:');
                $sheet->setCellValue('F' . $totalRow, $totalQuantity);
                $sheet->setCellValue('G' . $totalRow, 'item');
                $sheet->setCellValue('H' . $totalRow, '');
                $sheet->setCellValue('I' . $totalRow, $totalValue);
                
                // Style total row
                $sheet->getStyle('A' . $totalRow . ':I' . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFEB9C'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);
                
                // Format currency for total value
                $sheet->getStyle('I' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
