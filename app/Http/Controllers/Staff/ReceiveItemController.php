<?php

namespace App\Http\Controllers\Staff;

use App\Events\SubmissionCreated;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Submission;
use App\Models\SubmissionPhoto;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceiveItemController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $submissions = Submission::where('staff_id', $user->id)
            ->where('is_draft', false)
            ->with(['item', 'warehouse', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('staff.receive-items.index', compact('submissions'));
    }

    public function create()
    {
        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = auth()->user()->warehouses;
        
        return view('staff.receive-items.create', compact('items', 'suppliers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'nullable|exists:items,id',
            'item_name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'conversion_factor' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'nota_number' => 'nullable|string|max:100',
            'receive_date' => 'nullable|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'invoice_photo' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_draft' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $itemId = $validated['item_id'];
            $conversionFactor = $validated['conversion_factor'] ?? 1;
            
            // Jika item_id ada, validasi unit dan dapatkan conversion_factor
            if ($itemId) {
                $item = Item::with('units')->find($itemId);
                if ($item) {
                    // Validasi unit: harus base unit atau salah satu dari alternative units
                    $validUnits = [$item->unit];
                    foreach ($item->units as $unit) {
                        $validUnits[] = $unit->name;
                    }
                    
                    if (!in_array($validated['unit'], $validUnits)) {
                        return back()->withErrors([
                            'unit' => "Satuan '{$validated['unit']}' tidak valid untuk barang ini. Satuan yang tersedia: " . implode(', ', $validUnits)
                        ])->withInput();
                    }
                    
                    // Dapatkan conversion_factor berdasarkan unit yang dipilih
                    if ($validated['unit'] === $item->unit) {
                        $conversionFactor = 1;
                    } else {
                        $selectedUnit = $item->units->firstWhere('name', $validated['unit']);
                        if ($selectedUnit) {
                            $conversionFactor = $selectedUnit->conversion_factor;
                        }
                    }
                }
            }
            
            // Jika item_id tidak ada, berarti barang baru - buat item baru
            if (!$itemId && $validated['item_code'] && $validated['category_id']) {
                // Cek apakah kode sudah ada
                $existingItem = Item::where('code', $validated['item_code'])->first();
                if ($existingItem) {
                    return back()->withErrors(['item_code' => 'Kode barang sudah digunakan.'])->withInput();
                }
                
                // Tentukan satuan dasar (terkecil) berdasarkan satuan input
                $baseUnit = 'Pcs'; // Default satuan terkecil
                $inputUnit = $validated['unit'];
                $inputConversionFactor = $validated['conversion_factor'] ?? 1;
                
                // Mapping satuan besar ke satuan terkecil
                $unitMapping = [
                    'Lusin' => ['base' => 'Pcs', 'factor' => 12],
                    'Box' => ['base' => 'Pcs', 'factor' => 12],
                    'Gross' => ['base' => 'Pcs', 'factor' => 144],
                    'Pack' => ['base' => 'Pcs', 'factor' => 10],
                    'Rim' => ['base' => 'Lembar', 'factor' => 500],
                    'Pak' => ['base' => 'Lembar', 'factor' => 2500],
                    'Dus' => ['base' => 'Lembar', 'factor' => 500],
                    'Karton' => ['base' => 'Pcs', 'factor' => 24],
                ];
                
                // Jika satuan input ada di mapping, gunakan satuan dasar dari mapping
                if (isset($unitMapping[$inputUnit])) {
                    $baseUnit = $unitMapping[$inputUnit]['base'];
                    $conversionFactor = $unitMapping[$inputUnit]['factor'];
                } else {
                    // Jika tidak ada di mapping, anggap input adalah satuan dasar
                    $baseUnit = $inputUnit;
                    $conversionFactor = 1;
                }
                
                // Buat item baru dengan satuan dasar (terkecil)
                $newItem = Item::create([
                    'category_id' => $validated['category_id'],
                    'code' => $validated['item_code'],
                    'name' => $validated['item_name'],
                    'unit' => $baseUnit, // Simpan satuan terkecil sebagai base
                    'is_active' => true
                ]);
                
                $itemId = $newItem->id;
                
                // Jika input menggunakan satuan besar, buat item_unit untuk satuan tersebut
                if ($inputUnit !== $baseUnit) {
                    ItemUnit::create([
                        'item_id' => $itemId,
                        'name' => $inputUnit,
                        'conversion_factor' => $conversionFactor
                    ]);
                }
            }
            
            // Calculate total price
            $totalPrice = null;
            if ($request->filled('unit_price') && $request->filled('quantity')) {
                $totalPrice = $validated['unit_price'] * $validated['quantity'];
            }

            // Upload invoice photo if provided
            $invoicePhotoPath = null;
            if ($request->hasFile('invoice_photo')) {
                $invoicePhotoPath = $request->file('invoice_photo')->store('invoice-photos', 'public');
            }

            $submission = Submission::create([
                'item_id' => $itemId,
                'item_name' => $validated['item_name'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'unit_id' => $validated['warehouse_id'], // Set unit_id same as warehouse_id for compatibility
                'conversion_factor' => $conversionFactor,
                'unit_price' => $validated['unit_price'] ?? null,
                'total_price' => $totalPrice,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'warehouse_id' => $validated['warehouse_id'],
                'staff_id' => auth()->id(),
                'nota_number' => $validated['nota_number'] ?? null,
                'receive_date' => $validated['receive_date'] ?? now()->toDateString(),
                'notes' => $validated['notes'] ?? null,
                'invoice_photo' => $invoicePhotoPath,
                'status' => 'pending',
                'is_draft' => $request->boolean('is_draft', false),
                'submitted_at' => $request->boolean('is_draft') ? null : now(),
            ]);

            // Upload photos if provided
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('submission-photos', 'public');
                    
                    SubmissionPhoto::create([
                        'submission_id' => $submission->id,
                        'file_path' => $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'file_size' => $photo->getSize(),
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Dispatch event untuk notifikasi admin gudang jika bukan draft
            if (!$submission->is_draft) {
                event(new \App\Events\SubmissionCreated($submission));
            }

            DB::commit();

            $message = $submission->is_draft 
                ? 'Draft berhasil disimpan.' 
                : 'Submission berhasil dibuat dan menunggu verifikasi.';

            return redirect()->route('staff.receive-items.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $submission = Submission::with(['item', 'warehouse', 'supplier', 'photos', 'staff'])
            ->where('staff_id', auth()->id())
            ->findOrFail($id);
        
        return view('staff.receive-items.show', compact('submission'));
    }

    public function edit($id)
    {
        $submission = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->findOrFail($id);
        
        $items = Item::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = auth()->user()->warehouses;
        
        return view('staff.receive-items.edit', compact('submission', 'items', 'suppliers', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $submission = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->findOrFail($id);
        
        $validated = $request->validate([
            'item_id' => 'nullable|exists:items,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'conversion_factor' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:1000',
            'invoice_photo' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_draft' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $conversionFactor = $validated['conversion_factor'] ?? 1;
            
            // Validasi unit jika item_id ada
            if ($validated['item_id']) {
                $item = Item::with('units')->find($validated['item_id']);
                if ($item) {
                    $validUnits = [$item->unit];
                    foreach ($item->units as $unit) {
                        $validUnits[] = $unit->name;
                    }
                    
                    if (!in_array($validated['unit'], $validUnits)) {
                        return back()->withErrors([
                            'unit' => "Satuan '{$validated['unit']}' tidak valid untuk barang ini."
                        ])->withInput();
                    }
                    
                    // Dapatkan conversion_factor
                    if ($validated['unit'] === $item->unit) {
                        $conversionFactor = 1;
                    } else {
                        $selectedUnit = $item->units->firstWhere('name', $validated['unit']);
                        if ($selectedUnit) {
                            $conversionFactor = $selectedUnit->conversion_factor;
                        }
                    }
                }
            }
            // Calculate total price
            $totalPrice = null;
            if ($request->filled('unit_price') && $request->filled('quantity')) {
                $totalPrice = $validated['unit_price'] * $validated['quantity'];
            }

            // Upload new invoice photo if provided
            $invoicePhotoPath = $submission->invoice_photo;
            if ($request->hasFile('invoice_photo')) {
                // Delete old invoice photo
                if ($submission->invoice_photo) {
                    Storage::disk('public')->delete($submission->invoice_photo);
                }
                $invoicePhotoPath = $request->file('invoice_photo')->store('invoice-photos', 'public');
            }

            // Check if submission was draft before update
            $wasDraft = $submission->is_draft;

            $submission->update([
                'item_id' => $validated['item_id'] ?? null,
                'item_name' => $validated['item_name'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'unit_id' => $validated['warehouse_id'], // Set unit_id same as warehouse_id for compatibility
                'conversion_factor' => $conversionFactor,
                'unit_price' => $validated['unit_price'] ?? null,
                'total_price' => $totalPrice,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'warehouse_id' => $validated['warehouse_id'],
                'notes' => $validated['notes'] ?? null,
                'invoice_photo' => $invoicePhotoPath,
                'is_draft' => $request->boolean('is_draft', false),
                'submitted_at' => $request->boolean('is_draft') ? null : now(),
            ]);

            // Upload new photos if provided
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('submission-photos', 'public');
                    
                    SubmissionPhoto::create([
                        'submission_id' => $submission->id,
                        'photo_path' => $path,
                    ]);
                }
            }

            // Dispatch event untuk notifikasi admin gudang jika diubah dari draft ke submitted
            if ($wasDraft && !$submission->is_draft) {
                event(new \App\Events\SubmissionCreated($submission));
            }

            DB::commit();

            $message = $submission->is_draft 
                ? 'Draft berhasil diupdate.' 
                : 'Submission berhasil disubmit dan menunggu verifikasi.';

            return redirect()->route('staff.receive-items.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $submission = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->findOrFail($id);
        
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

    public function uploadPhoto(Request $request, $submission)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $submission = Submission::where('staff_id', auth()->id())->findOrFail($submission);
        
        try {
            $path = $request->file('photo')->store('submission-photos', 'public');
            
            $photo = SubmissionPhoto::create([
                'submission_id' => $submission->id,
                'photo_path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupload.',
                'photo' => $photo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload foto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePhoto(Request $request, $submission, $photo)
    {
        $submission = Submission::where('staff_id', auth()->id())->findOrFail($submission);
        $photo = SubmissionPhoto::where('submission_id', $submission->id)->findOrFail($photo);
        
        try {
            Storage::disk('public')->delete($photo->photo_path);
            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus foto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submit(Request $request, $submission)
    {
        $submission = Submission::where('staff_id', auth()->id())
            ->where('is_draft', true)
            ->findOrFail($submission);
        
        try {
            DB::transaction(function () use ($submission) {
                // Check if already submitted (safeguard against double submission)
                if ($submission->submitted_at !== null) {
                    return;
                }

                $submission->update([
                    'is_draft' => false,
                    'submitted_at' => now(),
                    'status' => 'pending'
                ]);

                // Dispatch event untuk notifikasi admin gudang
                event(new SubmissionCreated($submission));
            });

            return response()->json([
                'success' => true,
                'message' => 'Submission berhasil disubmit dan menunggu verifikasi.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $items = Item::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->with('category')
            ->limit(10)
            ->get();
        
        return response()->json($items->map(function($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category_name' => $item->category ? $item->category->name : 'Tanpa Kategori',
                'unit' => $item->unit
            ];
        }));
    }
    
    public function searchCategories(Request $request)
    {
        $query = $request->input('q');
        
        $categories = Category::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->orderBy('code')
            ->limit(20)
            ->get();
        
        return response()->json($categories->map(function($category) {
            return [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'level' => $category->level
            ];
        }));
    }
    
    public function generateItemCode(Request $request)
    {
        $categoryId = $request->input('category_id');
        $category = Category::find($categoryId);
        
        if (!$category) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }
        
        // Cari item terakhir dalam kategori ini
        $lastItem = Item::where('category_id', $categoryId)
            ->orderBy('code', 'desc')
            ->first();
        
        if ($lastItem) {
            // Parse nomor urut dari kode terakhir
            // Misal: 1.01.03.01.001 -> ambil 001
            $parts = explode('.', $lastItem->code);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
            $newCode = $category->code . '.' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            // Belum ada item di kategori ini, mulai dari 001
            $newCode = $category->code . '.001';
        }
        
        return response()->json(['code' => $newCode]);
    }

    /**
     * Get available units for an item
     */
    public function getItemUnits(Request $request)
    {
        $itemId = $request->input('item_id');
        
        if (!$itemId) {
            return response()->json(['units' => []]);
        }
        
        $item = Item::with('units')->find($itemId);
        
        if (!$item) {
            return response()->json(['units' => []]);
        }
        
        // Build units array: include base unit + alternative units
        $units = [];
        
        // Add base unit (conversion_factor = 1)
        $units[] = [
            'name' => $item->unit,
            'conversion_factor' => 1,
            'is_base' => true
        ];
        
        // Add alternative units
        foreach ($item->units as $unit) {
            $units[] = [
                'name' => $unit->name,
                'conversion_factor' => $unit->conversion_factor,
                'is_base' => false
            ];
        }
        
        return response()->json([
            'units' => $units,
            'base_unit' => $item->unit
        ]);
    }
}