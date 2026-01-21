<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DraftController extends Controller
{
    public function index()
    {
        $drafts = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->with(['item', 'warehouse', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('staff.drafts.index', compact('drafts'));
    }

    public function edit($submission)
    {
        $submission = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->findOrFail($submission);
        
        return redirect()->route('staff.receive-items.edit', $submission);
    }

    public function destroy($submission)
    {
        $submission = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->findOrFail($submission);
        
        DB::beginTransaction();
        try {
            // Delete photos
            foreach ($submission->photos as $photo) {
                Storage::disk('public')->delete($photo->photo_path);
                $photo->delete();
            }
            
            $submission->delete();
            
            DB::commit();
            
            return redirect()->route('staff.drafts')
                ->with('success', 'Draft berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}