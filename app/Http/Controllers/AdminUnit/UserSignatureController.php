<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\UserSignature;
use Illuminate\Http\Request;

class UserSignatureController extends Controller
{
    /**
     * Halaman kelola tanda tangan tersimpan.
     */
    public function show()
    {
        $signature = auth()->user()->savedSignature;
        return view('gudang.signature.index', compact('signature'));
    }

    /**
     * Simpan atau update tanda tangan default.
     */
    public function save(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string',
        ]);

        UserSignature::updateOrCreate(
            ['user_id' => auth()->id()],
            ['signature_data' => $request->signature_data]
        );

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan.');
    }

    /**
     * Hapus tanda tangan tersimpan.
     */
    public function destroy()
    {
        auth()->user()->savedSignature?->delete();
        return redirect()->back()->with('success', 'Tanda tangan berhasil dihapus.');
    }
}
