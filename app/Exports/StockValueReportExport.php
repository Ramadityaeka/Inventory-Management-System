<?php

namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockValueReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
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

        $unitPrice = $latestSubmission ? $latestSubmission->unit_price : 0;
        $totalValue = $stock->quantity * $unitPrice;

        return [
            $index,
            $stock->warehouse->name,
            $stock->item->code,
            $stock->item->name,
            $stock->item->category->name,
            $stock->quantity,
            $stock->item->unit,
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
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
