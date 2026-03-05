<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PublicRequest;
use App\Models\PublicRequestItem;
use App\Models\RequestSignature;
use App\Models\Stock;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicRequestController extends Controller
{
    /**
     * Tampilkan form pengajuan permintaan barang.
     */
    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        return view('public.request.create', compact('warehouses'));
    }

    /**
     * AJAX: Daftar barang tersedia di unit tertentu.
     */
    public function getStocks($id)
    {
        $stocks = Stock::where('warehouse_id', $id)
            ->where('quantity', '>', 0)
            ->whereHas('item', fn($q) => $q->where('is_active', true))
            ->with('item')
            ->get()
            ->map(fn($s) => [
                'item_id'  => $s->item_id,
                'name'     => $s->item->name,
                'quantity' => $s->quantity,
                'unit'     => $s->item->unit,
            ]);

        return response()->json($stocks);
    }

    /**
     * AJAX: Daftar PIC bertugas di unit tertentu.
     */
    public function getPics($id)
    {
        $pics = UserWarehouse::where('warehouse_id', $id)
            ->whereHas('user', fn($q) => $q
                ->where('role', 'admin_gudang')
                ->where('is_active', true)
            )
            ->with('user')
            ->get()
            ->map(fn($uw) => [
                'user_id' => $uw->user_id,
                'name'    => $uw->user->name,
            ]);

        return response()->json($pics);
    }

    /**
     * Simpan pengajuan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'requester_name' => 'required|string|max:255',
            'warehouse_id'   => 'required|exists:units,id',
            'pic_user_id'    => 'required|exists:users,id',
            'items'          => 'required|array|min:1',
            'items.*.item_id'  => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'signature_data' => 'required|string',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $publicRequest = DB::transaction(function () use ($request) {
            // Generate request_code
            $count = PublicRequest::whereDate('created_at', today())->count();
            $code  = 'REQ-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            // Generate UUID token
            $token = (string) Str::uuid();

            // Simpan public_request
            $publicRequest = PublicRequest::create([
                'request_code'   => $code,
                'token'          => $token,
                'requester_name' => $request->requester_name,
                'warehouse_id'   => $request->warehouse_id,
                'pic_user_id'    => $request->pic_user_id,
                'notes'          => $request->notes,
                'status'         => PublicRequest::STATUS_PENDING,
            ]);

            // Simpan items
            foreach ($request->items as $item) {
                PublicRequestItem::create([
                    'public_request_id'  => $publicRequest->id,
                    'item_id'            => $item['item_id'],
                    'quantity_requested' => $item['quantity'],
                ]);
            }

            // Simpan tanda tangan pemohon
            RequestSignature::create([
                'public_request_id' => $publicRequest->id,
                'signer_type'       => 'requester',
                'signer_name'       => $request->requester_name,
                'signature_data'    => $request->signature_data,
                'signed_at'         => now(),
                'ip_address'        => $request->ip(),
            ]);

            // Kirim notifikasi ke PIC
            Notification::create([
                'user_id'        => $publicRequest->pic_user_id,
                'type'           => 'public_request_new',
                'title'          => 'Permintaan Barang Baru',
                'message'        => "Ada permintaan barang dari {$publicRequest->requester_name}. Kode: {$publicRequest->request_code}.",
                'reference_type' => 'public_request',
                'reference_id'   => $publicRequest->id,
                'is_read'        => false,
            ]);

            return $publicRequest;
        });

        return redirect()->route('public.request.success', ['code' => $publicRequest->request_code]);
    }

    /**
     * Halaman sukses setelah submit.
     */
    public function success(Request $request)
    {
        $code = $request->query('code');
        return view('public.request.success', compact('code'));
    }

    /**
     * Form input kode untuk cek status.
     */
    public function checkStatus()
    {
        return view('public.request.status');
    }

    /**
     * Proses pencarian berdasarkan kode REQ.
     */
    public function findStatus(Request $request)
    {
        $request->validate(['request_code' => 'required|string']);

        $publicRequest = PublicRequest::where('request_code', $request->request_code)->first();

        if (!$publicRequest) {
            return redirect()->back()->withInput()->with('error', 'Kode tidak ditemukan. Pastikan kode yang Anda masukkan benar.');
        }

        return redirect()->route('public.request.document', ['token' => $publicRequest->token]);
    }

    /**
     * Tampilkan halaman dokumen permintaan.
     */
    public function document($token)
    {
        $publicRequest = PublicRequest::where('token', $token)
            ->with(['warehouse', 'pic', 'items.item', 'requesterSignature', 'picSignature'])
            ->firstOrFail();

        return view('public.request.document', compact('publicRequest'));
    }

    /**
     * Download PDF dokumen (hanya jika status completed).
     */
    public function exportPdf($token)
    {
        $publicRequest = PublicRequest::where('token', $token)
            ->with(['warehouse', 'pic', 'items.item', 'requesterSignature', 'picSignature'])
            ->firstOrFail();

        if (!$publicRequest->isCompleted()) {
            return redirect()->back()->with('error', 'Dokumen belum selesai. PDF hanya tersedia setelah PIC menandatangani.');
        }

        $gdAvailable = extension_loaded('gd');

        $pdf = Pdf::loadView('public-pdf.document', compact('publicRequest', 'gdAvailable'))
                  ->setPaper('a4', 'portrait')
                  ->setOption(['margin_top' => 20, 'margin_bottom' => 20, 'margin_left' => 30, 'margin_right' => 30]);

        return $pdf->download($publicRequest->request_code . '.pdf');
    }
}
