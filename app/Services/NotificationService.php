<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create notification dengan data dinamis
     * Transaction-safe: harus dipanggil dalam DB transaction
     */
    public function create(array $data): Notification
    {
        // Cek apakah ada notifikasi duplikat dalam 10 detik terakhir
        $recentDuplicate = Notification::where('user_id', $data['user_id'])
            ->where('type', $data['type'])
            ->where('message', $data['message'])
            ->where('reference_type', $data['reference_type'] ?? null)
            ->where('reference_id', $data['reference_id'] ?? null)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();
        
        // Jika ada duplikat, return yang sudah ada
        if ($recentDuplicate) {
            return $recentDuplicate;
        }
        
        return Notification::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'is_read' => false,
        ]);
    }

    /**
     * Notifikasi ketika user ditugaskan ke gudang
     */
    public function notifyWarehouseAssignment(User $user, array $warehouses, User $admin, string $action = 'assigned')
    {
        $warehouseNames = implode(', ', array_column($warehouses, 'name'));
        
        $message = $action === 'assigned' 
            ? "Anda telah ditugaskan ke gudang: {$warehouseNames} oleh {$admin->name}"
            : "Anda telah dihapus dari gudang: {$warehouseNames} oleh {$admin->name}";

        return $this->create([
            'user_id' => $user->id,
            'type' => 'warehouse_assignment',
            'title' => 'Penugasan Gudang',
            'message' => $message,
            'reference_type' => 'user',
            'reference_id' => $admin->id,
        ]);
    }

    /**
     * Notifikasi ketika role user berubah
     */
    public function notifyRoleChange(User $user, string $oldRole, string $newRole, User $admin)
    {
        $oldRoleText = $this->getRoleText($oldRole);
        $newRoleText = $this->getRoleText($newRole);

        return $this->create([
            'user_id' => $user->id,
            'type' => 'role_changed',
            'title' => 'Perubahan Role',
            'message' => "Role Anda telah diubah dari {$oldRoleText} menjadi {$newRoleText} oleh {$admin->name}",
            'reference_type' => 'user',
            'reference_id' => $admin->id,
        ]);
    }

    /**
     * Notifikasi ketika password direset
     */
    public function notifyPasswordReset(User $user, User $admin)
    {
        return $this->create([
            'user_id' => $user->id,
            'type' => 'password_reset',
            'title' => 'Password Direset',
            'message' => "Password Anda telah direset oleh {$admin->name}. Silakan login dengan password baru dan segera menggantinya.",
            'reference_type' => 'user',
            'reference_id' => $admin->id,
        ]);
    }

    /**
     * Notifikasi ketika status akun berubah
     */
    public function notifyStatusChange(User $user, bool $oldStatus, bool $newStatus, User $admin)
    {
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Akun Anda telah {$statusText} oleh {$admin->name}";

        return $this->create([
            'user_id' => $user->id,
            'type' => 'status_changed',
            'title' => 'Perubahan Status Akun',
            'message' => $message,
            'reference_type' => 'user',
            'reference_id' => $admin->id,
        ]);
    }

    /**
     * Notifikasi untuk admin gudang: submission baru
     */
    public function notifyAdminsNewSubmission($submission)
    {
        // Get all admin gudang yang punya akses ke warehouse submission
        $admins = User::where('role', User::ROLE_ADMIN_GUDANG)
            ->where('is_active', true)
            ->whereHas('warehouses', function($q) use ($submission) {
                $q->where('warehouses.id', $submission->warehouse_id);
            })
            ->get();

        $notifications = [];
        foreach ($admins as $admin) {
            $notifications[] = $this->create([
                'user_id' => $admin->id,
                'type' => 'new_submission',
                'title' => 'Submission Barang Baru',
                'message' => "Submission baru dari {$submission->staff->name} untuk item {$submission->item->name} ({$submission->quantity} {$submission->item->unit}) di {$submission->warehouse->name}",
                'reference_type' => 'submission',
                'reference_id' => $submission->id,
            ]);
        }

        return $notifications;
    }

    /**
     * Notifikasi untuk admin gudang: stock request baru
     */
    public function notifyAdminsNewStockRequest($stockRequest)
    {
        // Get all admin gudang yang punya akses ke warehouse request
        $admins = User::where('role', User::ROLE_ADMIN_GUDANG)
            ->where('is_active', true)
            ->whereHas('warehouses', function($q) use ($stockRequest) {
                $q->where('warehouses.id', $stockRequest->warehouse_id);
            })
            ->get();

        $notifications = [];
        foreach ($admins as $admin) {
            $notifications[] = $this->create([
                'user_id' => $admin->id,
                'type' => 'new_stock_request',
                'title' => 'Permintaan Barang Keluar Baru',
                'message' => "Permintaan barang keluar dari {$stockRequest->staff->name} untuk {$stockRequest->item->name} ({$stockRequest->quantity} {$stockRequest->item->unit}) di {$stockRequest->warehouse->name}",
                'reference_type' => 'stock_request',
                'reference_id' => $stockRequest->id,
            ]);
        }

        return $notifications;
    }

    /**
     * Notifikasi low stock untuk admin gudang
     */
    public function notifyAdminsLowStock($stock)
    {
        // Get all admin gudang yang punya akses ke warehouse
        $admins = User::where('role', User::ROLE_ADMIN_GUDANG)
            ->where('is_active', true)
            ->whereHas('warehouses', function($q) use ($stock) {
                $q->where('warehouses.id', $stock->warehouse_id);
            })
            ->get();

        $notifications = [];
        foreach ($admins as $admin) {
            $notifications[] = $this->create([
                'user_id' => $admin->id,
                'type' => 'low_stock_alert',
                'title' => 'Alert: Stok Habis',
                'message' => "Stok {$stock->item->name} di {$stock->warehouse->name} habis: {$stock->quantity} {$stock->item->unit}",
                'reference_type' => 'stock',
                'reference_id' => $stock->id,
            ]);
        }

        return $notifications;
    }

    /**
     * Get human-readable role text (dinamis dari konstanta model)
     */
    private function getRoleText(string $role): string
    {
        $roles = [
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_ADMIN_GUDANG => 'Admin Gudang',
            User::ROLE_STAFF_GUDANG => 'Staff Gudang',
        ];

        return $roles[$role] ?? ucwords(str_replace('_', ' ', $role));
    }
}
