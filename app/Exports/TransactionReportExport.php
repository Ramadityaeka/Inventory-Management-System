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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransactionReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithStrictNullComparison, WithEvents
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

        // Format tanggal dengan aman tanpa koma
        $submittedDate = $transaction->submitted_at ? 
            $transaction->submitted_at->format('d-m-Y H:i') : '-';

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                // Get all data to calculate totals
                $transactions = $this->collection();
                $totalQuantity = $transactions->sum('quantity');
                
                // Add empty row
                $totalRow = $lastRow + 1;
                
                // Add TOTAL KESELURUHAN row
                $sheet->setCellValue('A' . $totalRow, '');
                $sheet->setCellValue('B' . $totalRow, '');
                $sheet->setCellValue('C' . $totalRow, 'TOTAL KESELURUHAN:');
                $sheet->setCellValue('D' . $totalRow, $totalQuantity);
                $sheet->setCellValue('E' . $totalRow, 'item');
                $sheet->setCellValue('F' . $totalRow, '');
                $sheet->setCellValue('G' . $totalRow, '');
                $sheet->setCellValue('H' . $totalRow, '');
                $sheet->setCellValue('I' . $totalRow, '');
                $sheet->setCellValue('J' . $totalRow, '');
                
                // Style total row
                $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->applyFromArray([
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
                
                // Format number for total quantity
                $sheet->getStyle('D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
