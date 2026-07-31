<?php

use App\Models\User;
use App\Models\UserPreference;
use Inertia\Testing\AssertableInertia;

test('invoice settings page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('invoice.edit'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('settings/invoice')
            ->has('preference')
        );
});

test('invoice settings page is gated for guests', function () {
    $this->get(route('invoice.edit'))->assertRedirect('/login');
});

test('invoice defaults can be saved', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('invoice.edit'))
        ->post(route('invoice.update'), [
            'default_note' => 'Thank you for your business.',
            'default_bank_account_info' => 'ACME Bank'."\n".'0123456789',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('invoice.edit'));

    $preference = $user->refresh()->preference;
    expect($preference)->not->toBeNull()
        ->and($preference->default_note)->toBe('Thank you for your business.')
        ->and($preference->default_bank_account_info)->toBe('ACME Bank'."\n".'0123456789');
});

test('invoice defaults update existing preference without clobbering company fields', function () {
    $user = User::factory()->create();
    UserPreference::factory()->create([
        'user_id' => $user->id,
        'company_name' => 'Keep Me Inc.',
        'default_note' => 'Old note',
    ]);

    $this
        ->actingAs($user)
        ->post(route('invoice.update'), [
            'default_note' => 'New note',
            'default_bank_account_info' => 'Bank A',
        ])
        ->assertSessionHasNoErrors();

    $preference = $user->refresh()->preference;
    expect($preference->default_note)->toBe('New note')
        ->and($preference->default_bank_account_info)->toBe('Bank A')
        ->and($preference->company_name)->toBe('Keep Me Inc.');
});
