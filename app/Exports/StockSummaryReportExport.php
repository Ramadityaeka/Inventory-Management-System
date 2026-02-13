<?php

namespace App\Exports;

use App\Models\Submission;
use App\Models\StockRequest;
use App\Models\Stock;
use App\Models\Item;
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

class StockSummaryReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithStrictNullComparison
{
    protected $filters;
    protected $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        try {
            \Log::info('StockSummaryReportExport: Starting collection build', ['filters' => $this->filters]);
            
            // Base query untuk mengelompokkan per barang
            $query = Item::query()
                ->select([
                    'items.id',
                    'items.code',
                    'items.name',
                    'items.unit',
                    'items.category_id',
                    'categories.name as category_name'
                ])
                ->with('itemUnits')
                ->leftJoin('categories', 'items.category_id', '=', 'categories.id');

            // Apply filters
            if (isset($this->filters['category_id']) && !empty($this->filters['category_id'])) {
                $query->where('items.category_id', $this->filters['category_id']);
            }

            if (isset($this->filters['item_name']) && !empty($this->filters['item_name'])) {
                $query->where('items.name', 'LIKE', '%' . $this->filters['item_name'] . '%');
            }

            if (isset($this->filters['item_code']) && !empty($this->filters['item_code'])) {
                $query->where('items.code', 'LIKE', '%' . $this->filters['item_code'] . '%');
            }

                $items = $query->orderBy('items.code')->get();

            // Build summary data
            $summaryData = collect();
            $warehouseId = isset($this->filters['warehouse_id']) && !empty($this->filters['warehouse_id']) 
                ? $this->filters['warehouse_id'] : null;
            $warehouseIds = isset($this->filters['warehouse_ids']) && !empty($this->filters['warehouse_ids']) 
                ? $this->filters['warehouse_ids'] : null;
            $year = isset($this->filters['year']) && !empty($this->filters['year']) 
                ? $this->filters['year'] : null;
            $month = isset($this->filters['month']) && !empty($this->filters['month']) 
                ? $this->filters['month'] : null;
            
            // If warehouse_id is provided as filter, use it; otherwise use warehouse_ids
            $filterWarehouseIds = null;
            if ($warehouseId) {
                // Single warehouse filter (both super admin and gudang user can use this)
                $filterWarehouseIds = [$warehouseId];
            } elseif ($warehouseIds) {
                // Multiple warehouses (for gudang users)
                $filterWarehouseIds = is_array($warehouseIds) ? $warehouseIds : $warehouseIds->toArray();
            }

            foreach ($items as $item) {
                // Get unit information - use first unit from itemUnits or fallback to item.unit property
                $firstUnit = $item->itemUnits->first();
                $unitName = $firstUnit ? $firstUnit->name : ($item->unit ?? '-');

                // Get stock per warehouse
                $stocksQuery = Stock::where('item_id', $item->id)
                    ->where('quantity', '>', 0)
                    ->with('warehouse');
                
                // Apply warehouse filter if exists
                if ($filterWarehouseIds) {
                    $stocksQuery->whereIn('warehouse_id', $filterWarehouseIds);
                }
                
                $stocks = $stocksQuery->get();
                
                foreach ($stocks as $stock) {
                    // Recalculate stock in/out for this specific warehouse
                    $whStockIn = \App\Models\Submission::where('item_id', $item->id)
                        ->where('warehouse_id', $stock->warehouse_id)
                        ->where('status', 'approved')
                        ->whereNotNull('submitted_at')
                        ->when($year, fn($q) => $q->whereYear('submitted_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('submitted_at', $month))
                        ->sum('quantity') ?? 0;
                        
                    $whStockOut = \App\Models\StockRequest::where('item_id', $item->id)
                        ->where('warehouse_id', $stock->warehouse_id)
                        ->where('status', 'approved')
                        ->when($year, fn($q) => $q->whereYear('approved_at', $year))
                        ->when($month, fn($q) => $q->whereMonth('approved_at', $month))
                        ->sum('base_quantity') ?? 0;
                    
                    $summaryData->push((object)[
                        'warehouse_name' => $stock->warehouse->name ?? '-',
                        'code' => $item->code,
                        'name' => $item->name,
                        'category' => $item->category_name ?? '-',
                        'unit_in' => $unitName,
                        'stock_in' => $whStockIn,
                        'unit_out' => $unitName,
                        'stock_out' => $whStockOut,
                        'unit_stock' => $unitName,
                        'current_stock' => $stock->quantity,
                    ]);
                }
            }

            \Log::info('StockSummaryReportExport: Collection built successfully', ['count' => $summaryData->count()]);
            
            return $summaryData;
        
        } catch (\Exception $e) {
            \Log::error('StockSummaryReportExport: Error building collection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function headings(): array
    {
        return [
            'NO',
            'UNIT',
            'KODE BARANG',
            'NAMA BARANG',
            'KATEGORI',
            'SATUAN MASUK',
            'JUMLAH MASUK',
            'SATUAN KELUAR',
            'JUMLAH KELUAR',
            'SATUAN STOK',
            'SISA STOK',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $row->warehouse_name,
            $row->code,
            $row->name,
            $row->category,
            $row->unit_in,
            $row->stock_in,
            $row->unit_out,
            $row->stock_out,
            $row->unit_stock,
            $row->current_stock,
        ];
    }

    public function title(): string
    {
        return 'Ringkasan Stok';
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Get last row
        $lastRow = $this->rowNumber + 1;

        // All data borders
        $sheet->getStyle('A1:K' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align for NO and quantity columns
        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H2:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I2:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J2:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K2:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Zebra striping
        for ($i = 2; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':K' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'],
                    ],
                ]);
            }
        }

        return [];
    }
}
