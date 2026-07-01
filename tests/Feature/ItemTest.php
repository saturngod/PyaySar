<?php

use App\Models\Item;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('index page is displayed', function () {
    $items = Item::factory()->count(3)->create(['user_id' => $this->user->id]);

    get(route('items.index'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/index')
            ->has('items.data', 3)
        );
});

test('create page is displayed', function () {
    get(route('items.create'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/create')
        );
});

test('items can be stored', function () {
    $data = [
        'name' => 'Test Item',
        'price' => 100.50,
        'description' => 'Test Description',
    ];

    post(route('items.store'), $data)
        ->assertRedirect(route('items.index'))
        ->assertSessionHas('success', 'Item created successfully.');

    $this->assertDatabaseHas('items', [
        'name' => 'Test Item',
        'price' => 100.50,
        'description' => 'Test Description',
        'user_id' => $this->user->id,
    ]);
});

test('store validation fails', function () {
    post(route('items.store'), [])
        ->assertSessionHasErrors(['name', 'price']);
});

test('show page is displayed', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    get(route('items.show', $item))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/show')
            ->where('item.id', $item->id)
            ->where('item.name', $item->name)
        );
});

test('edit page is displayed', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    get(route('items.edit', $item))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('items/edit')
            ->where('item.id', $item->id)
        );
});

test('items can be updated', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    $data = [
        'name' => 'Updated Item',
        'price' => 200.00,
        'description' => 'Updated Description',
    ];

    put(route('items.update', $item), $data)
        ->assertRedirect(route('items.index'))
        ->assertSessionHas('success', 'Item updated successfully.');

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'name' => 'Updated Item',
        'price' => 200.00,
        'description' => 'Updated Description',
    ]);
});

test('items can be deleted', function () {
    $item = Item::factory()->create(['user_id' => $this->user->id]);

    delete(route('items.destroy', $item))
        ->assertRedirect(route('items.index'))
        ->assertSessionHas('success', 'Item deleted successfully.');

    $this->assertDatabaseMissing('items', ['id' => $item->id]);
});

test('user cannot view others items', function () {
    $otherUser = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $otherUser->id]);

    get(route('items.show', $item))
        ->assertForbidden();
});

test('user cannot edit others items', function () {
    $otherUser = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $otherUser->id]);

    get(route('items.edit', $item))
        ->assertForbidden();
});

test('user cannot update others items', function () {
    $otherUser = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $otherUser->id]);

    put(route('items.update', $item), [
        'name' => 'Hacker Update',
        'price' => 0,
    ])->assertForbidden();
});

test('user cannot delete others items', function () {
    $otherUser = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $otherUser->id]);

    delete(route('items.destroy', $item))
        ->assertForbidden();
});

test('guest cannot access items pages', function () {
    Auth::logout();

    get(route('items.index'))->assertRedirect(route('login'));
    get(route('items.create'))->assertRedirect(route('login'));
});
