<?php

namespace App\Exports;

use App\Models\Submission;
use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

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

        if (isset($this->filters['year']) && !empty($this->filters['year'])) {
            $query->whereYear('submitted_at', $this->filters['year']);
        }

        if (isset($this->filters['month']) && !empty($this->filters['month'])) {
            $query->whereMonth('submitted_at', $this->filters['month']);
        }

        if (isset($this->filters['warehouse_id']) && !empty($this->filters['warehouse_id'])) {
            $query->where('warehouse_id', $this->filters['warehouse_id']);
        }

        if (isset($this->filters['processed_by']) && !empty($this->filters['processed_by'])) {
            $query->whereHas('approvals', function($q) {
                $q->where('admin_id', $this->filters['processed_by']);
            });
        }

        if (isset($this->filters['status']) && !empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('submitted_at', 'desc')->get();
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
            'Sisa Stok',
            'Keterangan',
            'Status',
            'Diproses Oleh',
            'Role',
            'Waktu Transaksi'
        ];
    }

    public function map($transaction): array
    {
        static $index = 0;
        $index++;

        $currentStock = Stock::where('warehouse_id', $transaction->warehouse_id)
            ->where('item_id', $transaction->item_id)
            ->first();
        $remainingStock = $currentStock ? $currentStock->quantity : 0;
        $approval = $transaction->approvals->first();

        $statusText = 'Menunggu';
        if ($transaction->status == 'approved') {
            $statusText = 'Disetujui';
        } elseif ($transaction->status == 'rejected') {
            $statusText = 'Ditolak';
        }

        return [
            $index,
            $transaction->warehouse->name,
            $transaction->item->code,
            $transaction->item->name,
            $transaction->item->category->name,
            $transaction->quantity,
            $transaction->item->unit,
            $remainingStock,
            $transaction->notes ?: 'Penerimaan dari ' . ($transaction->supplier->name ?? '-'),
            $statusText,
            $approval ? $approval->admin->name : '-',
            $approval ? $approval->admin->role : '-',
            formatDateIndoLong($transaction->submitted_at) . ' WIB'
        ];
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
