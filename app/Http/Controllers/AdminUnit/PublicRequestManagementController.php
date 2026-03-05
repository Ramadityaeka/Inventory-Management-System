<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Models\PublicRequestItem;
use App\Models\RequestSignature;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicRequestManagementController extends Controller
{
    /**
     * Daftar permintaan publik masuk ke unit PIC.
     */
    public function index(Request $request)
    {
        $warehouseIds = auth()->user()->warehouses->pluck('id');

        $query = PublicRequest::whereIn('warehouse_id', $warehouseIds)
            ->with(['warehouse', 'pic', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('code')) {
            $query->where('request_code', 'like', '%' . $request->code . '%');
        }

        if ($request->filled('name')) {
            $query->where('requester_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('unit')) {
            $query->whereHas('warehouse', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->unit . '%');
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        $pendingCount = PublicRequest::whereIn('warehouse_id', $warehouseIds)
            ->where('status', PublicRequest::STATUS_PENDING)
            ->count();

        return view('gudang.public-requests.index', compact('requests', 'pendingCount'));
    }

    /**
     * Detail satu permintaan.
     */
    public function show($id)
    {
        $publicRequest = PublicRequest::findOrFail($id);

        // Cek akses warehouse
        if (!auth()->user()->warehouses->contains($publicRequest->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke unit ini.');
        }

        $publicRequest->load(['warehouse', 'pic', 'items.item', 'requesterSignature', 'picSignature']);

        return view('gudang.public-requests.show', compact('publicRequest'));
    }

    /**
     * Approve permintaan dan potong stok.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'items'                          => 'required|array',
            'items.*.item_id'                => 'required|exists:items,id',
            'items.*.quantity_approved'      => 'required|integer|min:0',
        ]);

        $publicRequest = PublicRequest::findOrFail($id);

        // Cek akses
        if (!auth()->user()->warehouses->contains($publicRequest->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke unit ini.');
        }

        // Cek status masih pending
        if ($publicRequest->status !== PublicRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $publicRequest) {
            $allFullyApproved = true;
            $anyApproved      = false;

            foreach ($request->items as $itemData) {
                $qtyApproved = (int) $itemData['quantity_approved'];

                // Ambil baris public_request_item
                $pri = PublicRequestItem::where('public_request_id', $publicRequest->id)
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                if (!$pri || $qtyApproved === 0) {
                    if ($pri) {
                        $pri->update(['quantity_approved' => 0]);
                    }
                    $allFullyApproved = false;
                    continue;
                }

                // Cek & potong stok dengan lockForUpdate
                $stock = Stock::where('item_id', $itemData['item_id'])
                    ->where('warehouse_id', $publicRequest->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->quantity < $qtyApproved) {
                    throw new \Exception("Stok tidak mencukupi untuk item ID {$itemData['item_id']}.");
                }

                $stock->decrement('quantity', $qtyApproved);

                $pri->update(['quantity_approved' => $qtyApproved]);

                // Catat StockMovement (quantity negatif = keluar, konsisten dgn konvensi sistem)
                StockMovement::create([
                    'item_id'        => $itemData['item_id'],
                    'unit_id'        => $publicRequest->warehouse_id,
                    'warehouse_id'   => $publicRequest->warehouse_id,
                    'movement_type'  => 'out',
                    'quantity'       => -$qtyApproved,
                    'reference_type' => 'public_request',
                    'reference_id'   => $publicRequest->id,
                    'notes'          => 'Permintaan publik #' . $publicRequest->request_code . ' - ' . $publicRequest->requester_name,
                    'created_by'     => auth()->id(),
                ]);

                if ($qtyApproved < $pri->quantity_requested) {
                    $allFullyApproved = false;
                }
                $anyApproved = true;
            }

            $newStatus = $allFullyApproved && $anyApproved
                ? PublicRequest::STATUS_APPROVED
                : PublicRequest::STATUS_PARTIAL;

            $publicRequest->update([
                'status'      => $newStatus,
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('gudang.public-requests.sign', $id)
            ->with('success', 'Permintaan berhasil disetujui. Silakan tandatangani dokumen.');
    }

    /**
     * Tolak permintaan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $publicRequest = PublicRequest::findOrFail($id);

        if (!auth()->user()->warehouses->contains($publicRequest->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke unit ini.');
        }

        if ($publicRequest->status !== PublicRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $publicRequest->update([
            'status'           => PublicRequest::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('gudang.public-requests.index')
            ->with('success', 'Permintaan berhasil ditolak.');
    }

    /**
     * Halaman tanda tangan PIC setelah approve.
     */
    public function showSign($id)
    {
        $publicRequest = PublicRequest::findOrFail($id);

        if (!auth()->user()->warehouses->contains($publicRequest->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke unit ini.');
        }

        if (!in_array($publicRequest->status, [
            PublicRequest::STATUS_APPROVED,
            PublicRequest::STATUS_PARTIAL,
        ])) {
            return redirect()->route('gudang.public-requests.index')
                ->with('error', 'Permintaan ini tidak dalam status yang dapat ditandatangani.');
        }

        $publicRequest->load(['warehouse', 'pic', 'items.item', 'requesterSignature']);

        $savedSignature = auth()->user()->savedSignature;

        return view('gudang.public-requests.sign', compact('publicRequest', 'savedSignature'));
    }

    /**
     * Simpan tanda tangan PIC & selesaikan dokumen.
     */
    public function saveSign(Request $request, $id)
    {
        $request->validate([
            'use_saved'       => 'nullable|boolean',
            'signature_data'  => 'required_if:use_saved,0|nullable|string',
            'save_as_default' => 'nullable|boolean',
        ]);

        $publicRequest = PublicRequest::findOrFail($id);

        if (!auth()->user()->warehouses->contains($publicRequest->warehouse_id)) {
            abort(403, 'Anda tidak memiliki akses ke unit ini.');
        }

        if (!in_array($publicRequest->status, [
            PublicRequest::STATUS_APPROVED,
            PublicRequest::STATUS_PARTIAL,
        ])) {
            return redirect()->route('gudang.public-requests.index')
                ->with('error', 'Permintaan ini tidak dapat ditandatangani.');
        }

        DB::transaction(function () use ($request, $publicRequest) {
            $useSaved = $request->boolean('use_saved');

            if ($useSaved) {
                $savedSig = auth()->user()->savedSignature;
                if (!$savedSig) {
                    throw new \Exception('Tidak ada tanda tangan tersimpan.');
                }
                $sigData = $savedSig->signature_data;
            } else {
                $sigData = $request->signature_data;
            }

            // Simpan tanda tangan PIC
            RequestSignature::create([
                'public_request_id' => $publicRequest->id,
                'signer_type'       => 'pic',
                'signer_name'       => auth()->user()->name,
                'signature_data'    => $sigData,
                'signed_at'         => now(),
                'ip_address'        => request()->ip(),
            ]);

            // Simpan sebagai default jika diminta
            if ($request->boolean('save_as_default')) {
                UserSignature::updateOrCreate(
                    ['user_id' => auth()->id()],
                    ['signature_data' => $sigData]
                );
            }

            // Selesaikan dokumen
            $publicRequest->update([
                'status'       => PublicRequest::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('gudang.public-requests.index')
            ->with('success', 'Dokumen berhasil diselesaikan dan ditandatangani.');
    }
}
