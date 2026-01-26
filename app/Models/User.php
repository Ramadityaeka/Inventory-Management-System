<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string $phone
 * @property bool $is_active
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN_GUDANG = 'admin_gudang';
    const ROLE_STAFF_GUDANG = 'staff_gudang';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouses')->withPivot('created_at');
    }
    
    // Alias untuk units (mendukung perubahan nama warehouse -> unit)
    public function units(): BelongsToMany
    {
        // Cek apakah tabel units sudah ada (setelah migration)
        try {
            return $this->belongsToMany(Unit::class, 'user_unit')->withPivot('created_at');
        } catch (\Exception $e) {
            // Fallback ke warehouses jika tabel units belum ada
            return $this->warehouses();
        }
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'staff_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'admin_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'requested_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    // Scopes
    public function scopeSuperAdmin($query)
    {
        return $query->where('role', self::ROLE_SUPER_ADMIN);
    }

    public function scopeAdminGudang($query)
    {
        return $query->where('role', self::ROLE_ADMIN_GUDANG);
    }

    public function scopeStaffGudang($query)
    {
        return $query->where('role', self::ROLE_STAFF_GUDANG);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper Methods
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminGudang(): bool
    {
        return $this->role === self::ROLE_ADMIN_GUDANG;
    }

    public function isStaffGudang(): bool
    {
        return $this->role === self::ROLE_STAFF_GUDANG;
    }

    public function hasAccessToWarehouse($warehouseId): bool
    {
        return $this->isSuperAdmin() || $this->warehouses()->where('warehouses.id', $warehouseId)->exists();
    }
}
