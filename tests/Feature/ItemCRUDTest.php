<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_items_index()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/items');

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }

    public function test_user_can_create_item()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/items', [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'price' => '99.99',
            'currency' => 'USD',
        ]);

        $response->assertRedirect('/items');
        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'price' => '99.99',
            'currency' => 'USD',
        ]);
    }

    public function test_user_can_edit_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/items/{$item->id}", [
            'name' => 'Updated Item',
            'description' => 'Updated Description',
            'price' => '199.99',
            'currency' => 'EUR',
        ]);

        $response->assertRedirect('/items');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Item',
            'price' => '199.99',
            'currency' => 'EUR',
        ]);
    }

    public function test_user_can_delete_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/items/{$item->id}");

        $response->assertRedirect('/items');
        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_cannot_access_other_users_items()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user1->id]);

        // User 2 trying to access User 1's item
        $response = $this->actingAs($user2)->get("/items/{$item->id}");

        $response->assertStatus(403);
    }
}