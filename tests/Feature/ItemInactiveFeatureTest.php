<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemInactiveFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_filter_items_by_status()
    {
        $category = Category::factory()->create();
        
        // Create items with different statuses
        $activeItem = Item::factory()->create(['is_active' => true]);
        $discontinuedItem = Item::factory()->create([
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_DISCONTINUED,
        ]);
        $seasonalItem = Item::factory()->create([
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_SEASONAL,
        ]);

        $this->actingAs($this->superAdmin);

        // Test filter: active
        $response = $this->get(route('admin.items.index', ['status' => 'active']));
        $response->assertSee($activeItem->name);
        $response->assertDontSee($discontinuedItem->name);

        // Test filter: discontinued
        $response = $this->get(route('admin.items.index', ['status' => 'discontinued']));
        $response->assertSee($discontinuedItem->name);
        $response->assertDontSee($activeItem->name);
    }

    /** @test */
    public function it_clears_stock_when_item_is_discontinued()
    {
        $category = Category::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $item = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        // Create stock for the item
        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'last_updated' => now(),
        ]);

        $this->actingAs($this->superAdmin);

        // Update item to discontinued
        $response = $this->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'min_threshold' => $item->min_threshold,
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_DISCONTINUED,
            'inactive_notes' => 'Test discontinued',
        ]);

        // Check stock is cleared
        $this->assertEquals(0, Stock::where('item_id', $item->id)->sum('quantity'));

        // Check stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'movement_type' => StockMovement::MOVEMENT_TYPE_ADJUSTMENT,
            'reference_type' => 'item_discontinued',
        ]);
    }

    /** @test */
    public function it_does_not_clear_stock_for_wrong_input_items()
    {
        $category = Category::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $item = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        // Create stock for the item
        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'last_updated' => now(),
        ]);

        $this->actingAs($this->superAdmin);

        // Update item to wrong_input
        $response = $this->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'min_threshold' => $item->min_threshold,
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_WRONG_INPUT,
            'inactive_notes' => 'Test wrong input',
        ]);

        // Check stock is NOT cleared
        $this->assertEquals(100, Stock::where('item_id', $item->id)->sum('quantity'));
    }

    /** @test */
    public function it_can_map_replacement_item_for_wrong_input()
    {
        $category = Category::factory()->create();
        $item = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);
        $replacementItem = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->superAdmin);

        // Update item with replacement
        $response = $this->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'min_threshold' => $item->min_threshold,
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_WRONG_INPUT,
            'replaced_by_item_id' => $replacementItem->id,
            'inactive_notes' => 'Wrong name, replaced',
        ]);

        $item->refresh();
        $this->assertEquals($replacementItem->id, $item->replaced_by_item_id);
    }

    /** @test */
    public function it_cannot_replace_item_with_itself()
    {
        $category = Category::factory()->create();
        $item = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->superAdmin);

        // Try to replace with self
        $response = $this->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'min_threshold' => $item->min_threshold,
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_WRONG_INPUT,
            'replaced_by_item_id' => $item->id,
        ]);

        $response->assertSessionHasErrors('replaced_by_item_id');
    }

    /** @test */
    public function it_can_reactivate_seasonal_items()
    {
        $category = Category::factory()->create();
        $item = Item::factory()->create([
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_SEASONAL,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->superAdmin);

        // Reactivate seasonal item
        $response = $this->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'min_threshold' => $item->min_threshold,
            'is_active' => true,
        ]);

        $item->refresh();
        $this->assertTrue($item->is_active);
        // Seasonal reason should be kept
        $this->assertEquals(Item::INACTIVE_REASON_SEASONAL, $item->inactive_reason);
    }

    /** @test */
    public function it_records_deactivation_audit_trail()
    {
        $category = Category::factory()->create();
        $item = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->superAdmin);

        // Deactivate item
        $response = $this->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category_id' => $item->category_id,
            'unit' => $item->unit,
            'min_threshold' => $item->min_threshold,
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_DISCONTINUED,
            'inactive_notes' => 'Test audit trail',
        ]);

        $item->refresh();
        $this->assertNotNull($item->deactivated_at);
        $this->assertEquals($this->superAdmin->id, $item->deactivated_by);
        $this->assertEquals('Test audit trail', $item->inactive_notes);
    }

    /** @test */
    public function scopes_work_correctly()
    {
        $category = Category::factory()->create();
        
        $activeItem = Item::factory()->create([
            'is_active' => true,
            'category_id' => $category->id,
        ]);
        
        $discontinuedItem = Item::factory()->create([
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_DISCONTINUED,
            'category_id' => $category->id,
        ]);
        
        $wrongInputItem = Item::factory()->create([
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_WRONG_INPUT,
            'category_id' => $category->id,
        ]);
        
        $seasonalActiveItem = Item::factory()->create([
            'is_active' => true,
            'inactive_reason' => Item::INACTIVE_REASON_SEASONAL,
            'category_id' => $category->id,
        ]);
        
        $seasonalInactiveItem = Item::factory()->create([
            'is_active' => false,
            'inactive_reason' => Item::INACTIVE_REASON_SEASONAL,
            'category_id' => $category->id,
        ]);

        // Test active scope
        $this->assertEquals(2, Item::active()->count());

        // Test discontinued scope
        $this->assertEquals(1, Item::discontinued()->count());

        // Test wrongInput scope
        $this->assertEquals(1, Item::wrongInput()->count());

        // Test seasonal scope (both active and inactive)
        $this->assertEquals(2, Item::seasonal()->count());
    }
}
