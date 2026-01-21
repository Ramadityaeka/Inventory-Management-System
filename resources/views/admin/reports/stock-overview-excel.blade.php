@extends('layouts.excel')

@section('content')
<table>
    <thead>
        <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Category</th>
            <th>Warehouse</th>
            <th>Current Stock</th>
            <th>Unit</th>
            <th>Harga Terakhir (Rp)</th>
            <th>Tanggal Pembelian</th>
            <th>Min Stock</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $stock)
            @php
                $lastPurchase = \App\Models\Submission::where('item_id', $stock->item_id)
                    ->where('status', 'approved')
                    ->whereNotNull('unit_price')
                    ->orderBy('created_at', 'desc')
                    ->first();
            @endphp
            <tr>
                <td>{{ $stock->item->code }}</td>
                <td>{{ $stock->item->name }}</td>
                <td>{{ $stock->item->category->name ?? 'N/A' }}</td>
                <td>{{ $stock->warehouse->name }}</td>
                <td class="text-center">{{ $stock->quantity }}</td>
                <td>{{ $stock->item->unit }}</td>
                <td class="text-right">
                    @if($lastPurchase)
                        {{ number_format($lastPurchase->unit_price, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($lastPurchase)
                        {{ $lastPurchase->created_at->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $stock->min_stock }}</td>
                <td class="text-center">
                    @if($stock->quantity <= 0)
                        Out of Stock
                    @elseif($stock->quantity <= $stock->min_stock)
                        Low Stock
                    @else
                        In Stock
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection