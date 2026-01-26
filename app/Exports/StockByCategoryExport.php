<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockByCategoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $categoryId;
    protected $warehouseId;

    public function __construct($categoryId = null, $warehouseId = null)
    {
        $this->categoryId = $categoryId;
        $this->warehouseId = $warehouseId;
    }

    public function collection()
    {
        $query = Stock::with(['item.category', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->select('stocks.*')
            ->orderBy('categories.name')
            ->orderBy('items.name')
            ->orderBy('warehouses.name');

        if ($this->categoryId) {
            $query->where('items.category_id', $this->categoryId);
        }

        if ($this->warehouseId) {
            $query->where('stocks.warehouse_id', $this->warehouseId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kategori',
            'Kode Item',
            'Nama Item',
            'Gudang',
            'Stok',
            'Satuan',
            'Status',
            'Terakhir Update'
        ];
    }

    public function map($stock): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $stock->item->category->name ?? '-',
            $stock->item->code,
            $stock->item->name,
            $stock->warehouse->name,
            $stock->quantity,
            $stock->item->unit,
            $this->getStatus($stock->quantity),
            $stock->last_updated ? $stock->last_updated->format('d/m/Y H:i') : '-'
        ];
    }

    private function getStatus($quantity): string
    {
        if ($quantity == 0) {
            return 'Habis';
        }
        return 'Tersedia';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Stok Per Kategori';
    }
}
