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
        // Get Submissions (Barang Masuk)
        $submissionsQuery = Submission::with([
            'item.category',
            'warehouse',
            'supplier',
            'staff',
            'approvals.admin'
        ])->whereNotNull('submitted_at');

        // Get Stock Requests (Barang Keluar)
        $stockRequestsQuery = \App\Models\StockRequest::with([
            'item.category',
            'warehouse',
            'staff',
            'approver'
        ])->where('status', 'approved');

        // Apply filters to both
        if (isset($this->filters['category_id']) && !empty($this->filters['category_id'])) {
            $submissionsQuery->whereHas('item', function($q) {
                $q->where('category_id', $this->filters['category_id']);
            });
            $stockRequestsQuery->whereHas('item', function($q) {
                $q->where('category_id', $this->filters['category_id']);
            });
        }

        if (isset($this->filters['item_name']) && !empty($this->filters['item_name'])) {
            $submissionsQuery->whereHas('item', function($q) {
                $q->where('name', 'LIKE', '%' . $this->filters['item_name'] . '%');
            });
            $stockRequestsQuery->whereHas('item', function($q) {
                $q->where('name', 'LIKE', '%' . $this->filters['item_name'] . '%');
            });
        }

        if (isset($this->filters['item_code']) && !empty($this->filters['item_code'])) {
            $submissionsQuery->whereHas('item', function($q) {
                $q->where('code', 'LIKE', '%' . $this->filters['item_code'] . '%');
            });
            $stockRequestsQuery->whereHas('item', function($q) {
                $q->where('code', 'LIKE', '%' . $this->filters['item_code'] . '%');
            });
        }

        if (isset($this->filters['year']) && !empty($this->filters['year'])) {
            $submissionsQuery->whereYear('submitted_at', $this->filters['year']);
            $stockRequestsQuery->whereYear('created_at', $this->filters['year']);
        }

        if (isset($this->filters['month']) && !empty($this->filters['month'])) {
            $submissionsQuery->whereMonth('submitted_at', $this->filters['month']);
            $stockRequestsQuery->whereMonth('created_at', $this->filters['month']);
        }

        if (isset($this->filters['warehouse_id']) && !empty($this->filters['warehouse_id'])) {
            $submissionsQuery->where('warehouse_id', $this->filters['warehouse_id']);
            $stockRequestsQuery->where('warehouse_id', $this->filters['warehouse_id']);
        }

        // Support for multiple warehouse IDs (for admin gudang)
        if (isset($this->filters['warehouse_ids']) && !empty($this->filters['warehouse_ids'])) {
            $submissionsQuery->whereIn('warehouse_id', $this->filters['warehouse_ids']);
            $stockRequestsQuery->whereIn('warehouse_id', $this->filters['warehouse_ids']);
        }

        // Apply status filter only to submissions
        if (isset($this->filters['status']) && !empty($this->filters['status'])) {
            $submissionsQuery->where('status', $this->filters['status']);
        }

        // Get results
        $submissions = $submissionsQuery->get()->map(function($item) {
            $item->transaction_type = 'in';
            $item->transaction_date = $item->submitted_at;
            return $item;
        });

        // Only get stock requests if status is empty or approved
        $stockRequests = collect([]);
        if (!isset($this->filters['status']) || empty($this->filters['status']) || $this->filters['status'] == 'approved') {
            $stockRequests = $stockRequestsQuery->get()->map(function($item) {
                $item->transaction_type = 'out';
                $item->transaction_date = $item->approved_at ?? $item->created_at;
                return $item;
            });
        }

        return $submissions->concat($stockRequests)->sortByDesc('transaction_date');
    }

    public function headings(): array
    {
        return [
            'No',
            'Unit',
            'Nama Barang',
            'Barang Masuk',
            'Barang Keluar',
            'Satuan',
            'Stok Saat Ini',
            'Keterangan',
            'Diajukan Oleh',
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

        // Handle different transaction types
        if ($transaction->transaction_type == 'in') {
            // Barang Masuk (Submission)
            $approval = $transaction->approvals->first();
            $statusText = 'Menunggu';
            if ($transaction->status == 'approved') {
                $statusText = 'Disetujui';
            } elseif ($transaction->status == 'rejected') {
                $statusText = 'Ditolak';
            }
            
            $barangMasuk = (int) $transaction->quantity;
            $barangKeluar = 0;
            $keterangan = $transaction->notes ?: ('Penerimaan dari ' . ($transaction->supplier->name ?? '-'));
            $keterangan = str_replace([',', '"', "\n", "\r"], [' ', '', ' ', ' '], $keterangan);
            $diproses = $approval ? $approval->admin->name : '-';
            $transactionDate = $transaction->submitted_at ? 
                $transaction->submitted_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
        } else {
            // Barang Keluar (StockRequest)
            $barangMasuk = 0;
            $barangKeluar = (int) ($transaction->base_quantity ?? $transaction->quantity);
            $statusText = 'Disetujui';
            $keterangan = $transaction->purpose ?? 'Penggunaan barang';
            $keterangan = str_replace([',', '"', "\n", "\r"], [' ', '', ' ', ' '], $keterangan);
            $diproses = $transaction->approver ? $transaction->approver->name : '-';
            $transactionDate = $transaction->approved_at ? 
                $transaction->approved_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : 
                ($transaction->created_at ? $transaction->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-');
        }

        return [
            $index,
            $transaction->warehouse->name ?? '-',
            $transaction->item->name ?? '-',
            $barangMasuk,
            $barangKeluar,
            $transaction->item->unit ?? '-',
            (int) $remainingStock,
            $keterangan,
            $transaction->staff ? $transaction->staff->name : '-',
            $statusText,
            $diproses,
            $transactionDate
        ];
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:L1')->applyFromArray([
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
            $sheet->getStyle('A2:L' . $lastRow)->applyFromArray([
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
