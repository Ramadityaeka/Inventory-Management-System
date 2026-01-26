<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DetailedStockExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Stock::with(['item.category', 'item.supplier', 'warehouse'])
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('suppliers', 'items.supplier_id', '=', 'suppliers.id')
            ->select('stocks.*')
            ->orderBy('warehouses.name')
            ->orderBy('categories.name')
            ->orderBy('items.name');

        // Apply filters
        if (!empty($this->filters['warehouse_id'])) {
            $query->where('stocks.warehouse_id', $this->filters['warehouse_id']);
        }

        if (!empty($this->filters['category_id'])) {
            $query->where('items.category_id', $this->filters['category_id']);
        }

        if (!empty($this->filters['supplier_id'])) {
            $query->where('items.supplier_id', $this->filters['supplier_id']);
        }

        if (!empty($this->filters['status'])) {
            if ($this->filters['status'] == 'out') {
                $query->where('stocks.quantity', '=', 0);
            } elseif ($this->filters['status'] == 'available') {
                $query->where('stocks.quantity', '>', 0);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Gudang',
            'Kategori',
            'Supplier',
            'Kode Item',
            'Nama Item',
            'Deskripsi',
            'Stok',
            'Satuan',
            'Status',
            'Harga Terakhir (Rp)',
            'Nilai Stok (Rp)',
            'Item Aktif',
            'Terakhir Update'
        ];
    }

    public function map($stock): array
    {
        static $no = 0;
        $no++;

        // Get latest price from submissions
        $latestSubmission = \App\Models\Submission::where('item_id', $stock->item_id)
            ->where('warehouse_id', $stock->warehouse_id)
            ->where('status', 'approved')
            ->whereNotNull('unit_price')
            ->orderBy('created_at', 'desc')
            ->first();

        $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
        $stockValue = $stock->quantity * $unitPrice;

        return [
            $no,
            $stock->warehouse->name,
            $stock->item->category->name ?? '-',
            $stock->item->supplier->name ?? '-',
            $stock->item->code,
            $stock->item->name,
            $stock->item->description ?? '-',
            $stock->quantity,
            $stock->item->unit,
            $this->getStatus($stock->quantity),
            $unitPrice,
            $stockValue,
            $stock->item->is_active ? 'Ya' : 'Tidak',
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
                    'startColor' => ['rgb' => '5B9BD5']
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

    public function columnFormats(): array
    {
        return [
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Harga Terakhir
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Nilai Stok
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok Detail';
    }
}
