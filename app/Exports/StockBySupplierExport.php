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

class StockBySupplierExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $supplierId;

    public function __construct($supplierId = null)
    {
        $this->supplierId = $supplierId;
    }

    public function collection()
    {
        $query = Stock::with(['item.category', 'item.supplier', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->leftJoin('suppliers', 'items.supplier_id', '=', 'suppliers.id')
            ->select('stocks.*')
            ->orderBy('suppliers.name')
            ->orderBy('items.name')
            ->orderBy('stocks.warehouse_id');

        if ($this->supplierId) {
            $query->where('items.supplier_id', $this->supplierId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Supplier',
            'Kode Item',
            'Nama Item',
            'Kategori',
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
            $stock->item->supplier->name ?? 'Tanpa Supplier',
            $stock->item->code,
            $stock->item->name,
            $stock->item->category->name ?? '-',
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
                    'startColor' => ['rgb' => 'FFC000']
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
        return 'Stok Per Supplier';
    }
}
