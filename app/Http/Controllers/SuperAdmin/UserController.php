<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Events\UserPasswordReset;
use App\Events\UserRoleChanged;
use App\Events\UserStatusChanged;
use App\Events\UserWarehouseAssigned;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('warehouses');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $warehouses = Warehouse::get();
        return view('admin.users.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_GUDANG, User::ROLE_STAFF_GUDANG])],
            'phone' => 'nullable|string|max:20',
            'warehouse' => 'nullable|required_if:role,admin_gudang|exists:warehouses,id',
            'warehouses' => 'nullable|required_if:role,staff_gudang|array',
            'warehouses.*' => 'nullable|exists:warehouses,id',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
            ]);

            // Sync warehouses based on role
            $warehouseNames = [];
            if ($validated['role'] === User::ROLE_ADMIN_GUDANG) {
                // Admin gudang only gets one warehouse
                $user->warehouses()->sync([$validated['warehouse']]);
                $warehouse = Warehouse::find($validated['warehouse']);
                if ($warehouse) {
                    $warehouseNames[] = $warehouse->name;
                }
            } elseif ($validated['role'] === User::ROLE_STAFF_GUDANG) {
                // Staff gudang can have multiple warehouses
                $user->warehouses()->sync($validated['warehouses']);
                $warehouses = Warehouse::whereIn('id', $validated['warehouses'])->get();
                $warehouseNames = $warehouses->pluck('name')->toArray();
            }

            // Create welcome notification
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Selamat Datang di Inventory ESDM',
                'message' => 'Akun Anda telah berhasil dibuat oleh Super Admin. Anda sekarang dapat login ke sistem.',
                'type' => 'info',
            ]);

            // Create warehouse assignment notification
            if (!empty($warehouseNames)) {
                $roleText = $validated['role'] === User::ROLE_ADMIN_GUDANG ? 'Admin Gudang' : 'Staff Gudang';
                $warehouseList = implode(', ', $warehouseNames);
                
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Penugasan Gudang',
                    'message' => "Anda telah ditugaskan sebagai {$roleText} untuk mengelola gudang: {$warehouseList}.",
                    'type' => 'info',
                ]);
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $warehouses = Warehouse::get();
        $user->load('warehouses');

        return view('admin.users.edit', compact('user', 'warehouses'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_GUDANG, User::ROLE_STAFF_GUDANG])],
            'phone' => 'nullable|string|max:20',
            'warehouse' => 'nullable|required_if:role,admin_gudang|exists:warehouses,id',
            'warehouses' => 'nullable|required_if:role,staff_gudang|array',
            'warehouses.*' => 'nullable|exists:warehouses,id',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $user, $request) {
            // Track changes for events
            $oldWarehouses = $user->warehouses->pluck('id')->toArray();
            $oldRole = $user->role;
            $oldStatus = $user->is_active;
            
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $request->has('is_active'),
            ];

            // Update password if provided - dispatch event
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
                event(new UserPasswordReset($user, auth()->user()));
            }

            // Check for role change - dispatch event
            if ($oldRole !== $validated['role']) {
                event(new UserRoleChanged($user, $oldRole, $validated['role'], auth()->user()));
            }

            // Check for status change - dispatch event
            $newStatus = $request->has('is_active');
            if ($oldStatus !== $newStatus) {
                event(new UserStatusChanged($user, $oldStatus, $newStatus, auth()->user()));
            }

            $user->update($updateData);

            // Sync warehouses based on role and dispatch events
            $newWarehouses = [];
            if ($validated['role'] === User::ROLE_ADMIN_GUDANG) {
                // Admin gudang only gets one warehouse
                $user->warehouses()->sync([$validated['warehouse']]);
                $newWarehouses = [$validated['warehouse']];
            } elseif ($validated['role'] === User::ROLE_STAFF_GUDANG) {
                // Staff gudang can have multiple warehouses
                $user->warehouses()->sync($validated['warehouses']);
                $newWarehouses = $validated['warehouses'] ?? [];
            } else {
                // Super admin has no warehouse assignments
                $user->warehouses()->detach();
                $newWarehouses = [];
            }

            // Check for warehouse changes - dispatch events
            sort($oldWarehouses);
            sort($newWarehouses);
            
            if ($oldWarehouses !== $newWarehouses) {
                $added = array_diff($newWarehouses, $oldWarehouses);
                $removed = array_diff($oldWarehouses, $newWarehouses);
                
                if (!empty($added)) {
                    foreach ($added as $warehouseId) {
                        $warehouse = Warehouse::find($warehouseId);
                        if ($warehouse) {
                            event(new UserWarehouseAssigned($user, $warehouse, auth()->user(), 'assigned'));
                        }
                    }
                }
                
                if (!empty($removed)) {
                    foreach ($removed as $warehouseId) {
                        $warehouse = Warehouse::find($warehouseId);
                        if ($warehouse) {
                            event(new UserWarehouseAssigned($user, $warehouse, auth()->user(), 'removed'));
                        }
                    }
                }
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent user from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }
        // Permanently remove user and related pivot/notifications in a transaction
        DB::transaction(function () use ($user) {
            // Detach any warehouse relations
            try {
                $user->warehouses()->detach();
            } catch (\Exception $e) {
                // ignore if relation not set
            }

            // Delete user notifications
            Notification::where('user_id', $user->id)->delete();

            // Finally delete the user record
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $oldStatus = $user->is_active;
            
            // Get the desired status from request, fallback to toggle if not provided
            $newStatus = $request->has('is_active') 
                ? $request->boolean('is_active') 
                : !$user->is_active;
            
            $user->update(['is_active' => $newStatus]);

            // Dispatch event for status change
            if ($oldStatus !== $newStatus) {
                event(new UserStatusChanged($user, $oldStatus, $newStatus, auth()->user()));
            }
        });

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "User {$status} successfully.",
            'is_active' => $user->is_active,
        ]);
    }

    public function resetPassword(User $user)
    {
        DB::transaction(function () use ($user) {
            // Generate random 8 character password
            $newPassword = Str::random(8);

            // Hash and save
            $user->update(['password' => Hash::make($newPassword)]);

            // Dispatch password reset event
            event(new UserPasswordReset($user, auth()->user()));
            
            // Store the new password in session for display
            session()->flash('new_password', $newPassword);
        });

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. User has been notified.',
            'new_password' => session('new_password'),
        ]);
    }
}