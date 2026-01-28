<?php

namespace App\Exports;

use App\Models\Submission;
use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransactionReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithStrictNullComparison
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

        // Support for multiple warehouse IDs (for admin gudang)
        if (isset($this->filters['warehouse_ids']) && !empty($this->filters['warehouse_ids'])) {
            $query->whereIn('warehouse_id', $this->filters['warehouse_ids']);
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
            'Nama Barang',
            'Jumlah',
            'Satuan',
            'Sisa Stok',
            'Keterangan',
            'Status',
            'Diproses Oleh',
            'Waktu'
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

        // Format tanggal dengan timezone Asia/Jakarta (WIB)
        $submittedDate = $transaction->submitted_at ? 
            $transaction->submitted_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';

        // Clean keterangan dari karakter bermasalah
        $keterangan = $transaction->notes ?: ('Penerimaan dari ' . ($transaction->supplier->name ?? '-'));
        $keterangan = str_replace([',', '"', "\n", "\r"], [' ', '', ' ', ' '], $keterangan);

        return [
            $index,
            $transaction->warehouse->name ?? '-',
            $transaction->item->name ?? '-',
            (int) $transaction->quantity,
            $transaction->item->unit ?? '-',
            (int) $remainingStock,
            $keterangan,
            $statusText,
            $approval ? $approval->admin->name : '-',
            $submittedDate
        ];
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:J1')->applyFromArray([
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
            $sheet->getStyle('A2:J' . $lastRow)->applyFromArray([
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
}
