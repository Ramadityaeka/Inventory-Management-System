<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\Submission;
use App\Models\SubmissionApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    /**
     * Display submissions for user's warehouses
     */
    public function index(Request $request)
    {
        try {
            // Get user's warehouse IDs from auth user warehouses relationship
            $user = Auth::user();
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

            if (empty($warehouseIds)) {
                return view('gudang.submissions.index', [
                    'submissions' => collect(),
                    'pendingCount' => 0,
                    'currentStatus' => 'pending'
                ]);
            }

            // Query submissions where warehouse_id in user's warehouses and is_draft = false
            $query = Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('is_draft', false);

            // Accept status filter (default: pending)
            $status = $request->get('status', 'pending');
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            // Load relationships and order
            $submissions = $query
                ->with(['item', 'staff', 'supplier', 'warehouse', 'approvals.admin'])
                ->orderBy('submitted_at', 'desc')
                ->paginate(20);

            // Count pending submissions for user's warehouses
            $pendingCount = Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('is_draft', false)
                ->where('status', 'pending')
                ->count();

            return view('gudang.submissions.index', [
                'submissions' => $submissions,
                'pendingCount' => $pendingCount,
                'currentStatus' => $status
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading submissions: ' . $e->getMessage());
        }
    }

    /**
     * Display submission details
     */
    public function show(Submission $submission)
    {
        try {
            // Authorize: check user hasAccessToWarehouse(submission warehouse_id), abort 403 if not
            if (!Auth::user()->hasAccessToWarehouse($submission->warehouse_id)) {
                abort(403, 'Unauthorized access to this warehouse submission.');
            }

            // Load relationships: item, category, staff, supplier, warehouse, photos
            $submission->load([
                'item.category',
                'staff',
                'supplier', 
                'warehouse',
                'photos',
                'approvals.admin'
            ]);

            return view('gudang.submissions.show', compact('submission'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading submission details: ' . $e->getMessage());
        }
    }

    /**
     * Approve submission
     */
    public function approve(Submission $submission)
    {
        try {
            // Check if item is inactive
            if (!$submission->item->is_active) {
                $reason = $submission->item->inactive_reason;
                $reasonText = $reason === 'discontinued' ? 'tidak diproduksi lagi' : 
                             ($reason === 'wrong_input' ? 'salah input' : 'musiman (inactive)');
                return redirect()->back()->with('error', "Tidak dapat approve submission. Barang {$submission->item->name} sudah dinonaktifkan ({$reasonText}).");
            }
            // Authorize warehouse access
            if (!Auth::user()->hasAccessToWarehouse($submission->warehouse_id)) {
                abort(403, 'Unauthorized access to this warehouse submission.');
            }

            // Validate submission status must be 'pending'
            if ($submission->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending submissions can be approved.');
            }

            DB::beginTransaction();

            // Create approval record
            $approval = Approval::create([
                'submission_id' => $submission->id,
                'admin_id' => Auth::id(),
                'action' => 'approved',
                'notes' => 'Approved by admin',
            ]);

            // Update submission status
            $submission->update(['status' => 'approved']);

            // NOTE: Trigger after_insert_approvals will automatically:
            // * Update stocks +quantity  
            // * Insert stock_movements

            // Create notification to staff
            $this->createStaffNotification($submission, 'approved', 'Submission Anda telah diapprove oleh admin gudang.');

            DB::commit();

            return redirect()->back()->with('success', 'Submission berhasil diapprove. Stok otomatis diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error approving submission: ' . $e->getMessage());
        }
    }

    /**
     * Reject submission with reason
     */
    public function reject(Request $request, Submission $submission)
    {
        try {
            // Authorize warehouse access
            if (!Auth::user()->hasAccessToWarehouse($submission->warehouse_id)) {
                abort(403, 'Unauthorized access to this warehouse submission.');
            }

            // Validate rejection_reason required (enum values or string), notes max 500
            $request->validate([
                'rejection_reason' => [
                    'required',
                    Rule::in(['incomplete_data', 'invalid_quantity', 'duplicate_entry', 'item_not_found', 'supplier_issue', 'other'])
                ],
                'notes' => 'nullable|string|max:500'
            ]);

            // Validate submission status = 'pending'
            if ($submission->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending submissions can be rejected.');
            }

            DB::beginTransaction();

            // Create approval record with rejection
            $approval = Approval::create([
                'submission_id' => $submission->id,
                'admin_id' => Auth::id(),
                'action' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'notes' => $request->notes ?? $this->getRejectionReasonText($request->rejection_reason),
            ]);

            // Update submission status
            $submission->update(['status' => 'rejected']);

            // Create notification to staff with rejection details
            $rejectionText = $this->getRejectionReasonText($request->rejection_reason);
            $notificationMessage = "Submission Anda ditolak. Alasan: {$rejectionText}";
            if ($request->notes) {
                $notificationMessage .= " - " . $request->notes;
            }

            $this->createStaffNotification($submission, 'rejected', $notificationMessage);

            DB::commit();

            return redirect()->back()->with('success', 'Submission berhasil ditolak. Staff akan mendapat notifikasi.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error rejecting submission: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for staff
     */
    private function createStaffNotification(Submission $submission, string $action, string $message)
    {
        try {
            // Create notification record for the staff who submitted
            if ($submission->staff_id) {
                \App\Models\Notification::create([
                    'user_id' => $submission->staff_id,
                    'type' => 'submission_' . $action,
                    'title' => 'Submission ' . ucfirst($action),
                    'message' => $message,
                    'data' => json_encode([
                        'submission_id' => $submission->id,
                        'item_name' => $submission->item ? $submission->item->name : $submission->item_name,
                        'quantity' => $submission->quantity,
                        'action' => $action,
                        'admin_name' => Auth::user()->name
                    ]),
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::warning('Failed to create staff notification: ' . $e->getMessage());
        }
    }

    /**
     * Get human readable rejection reason text
     */
    private function getRejectionReasonText(string $reason): string
    {
        $reasons = [
            'incomplete_data' => 'Data tidak lengkap atau tidak valid',
            'invalid_quantity' => 'Jumlah quantity tidak sesuai atau berlebihan',
            'duplicate_entry' => 'Data duplikat, submission serupa sudah ada',
            'item_not_found' => 'Item tidak ditemukan dalam sistem',
            'supplier_issue' => 'Masalah dengan data supplier',
            'other' => 'Alasan lainnya'
        ];

        return $reasons[$reason] ?? 'Alasan tidak diketahui';
    }

    /**
     * Bulk approve multiple submissions
     */
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'submission_ids' => 'required|array',
                'submission_ids.*' => 'integer|exists:submissions,id'
            ]);

            $user = Auth::user();
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

            if (empty($warehouseIds)) {
                return redirect()->back()->with('error', 'No warehouse access.');
            }

            $submissions = Submission::whereIn('id', $request->submission_ids)
                ->whereIn('warehouse_id', $warehouseIds)
                ->where('status', 'pending')
                ->get();

            if ($submissions->isEmpty()) {
                return redirect()->back()->with('error', 'No valid pending submissions found.');
            }

            DB::beginTransaction();

            $approvedCount = 0;
            foreach ($submissions as $submission) {
                // Create approval record
                SubmissionApproval::create([
                    'submission_id' => $submission->id,
                    'admin_id' => Auth::id(),
                    'action' => 'approved',
                    'notes' => 'Bulk approved by admin',
                    'created_at' => now()
                ]);

                // Create notification
                $this->createStaffNotification($submission, 'approved', 'Submission Anda telah diapprove secara bulk oleh admin gudang.');

                $approvedCount++;
            }

            DB::commit();

            return redirect()->back()->with('success', "Successfully approved {$approvedCount} submission(s). Stock automatically updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error in bulk approve: ' . $e->getMessage());
        }
    }

    /**
     * Get submission statistics for dashboard
     */
    public function statistics()
    {
        return $this->getStatistics();
    }

    /**
     * Get submission statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $user = Auth::user();
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

            if (empty($warehouseIds)) {
                return response()->json([
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'total' => 0
                ]);
            }

            $baseQuery = Submission::whereIn('warehouse_id', $warehouseIds)
                ->where('is_draft', false);

            $stats = [
                'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
                'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
                'total' => (clone $baseQuery)->count()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}