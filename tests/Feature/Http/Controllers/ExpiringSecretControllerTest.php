<?php

use App\Models\ExpiringSecret;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

test('creates an encrypted secret with the requested expiry', function (string $unit, string $expectedExpiry) {
    $this->travelTo('2026-01-01 12:00:00');

    $response = $this->post(route('store'), [
        'content' => 'private message',
        'expires_in' => 1,
        'expires_unit' => $unit,
        'remaining_views' => 2,
    ]);

    $response->assertOk()->assertViewIs('expiringSecret.store');
    $secret = ExpiringSecret::query()->sole();
    expect($secret->content)->toBe('private message');
    expect($secret->expires_at->format('Y-m-d H:i:s'))->toBe($expectedExpiry);
    expect($secret->remaining_views)->toBe(2);
    expect(DB::table('expiring_secrets')->value('content'))->not->toContain('private message');
    $response->assertSee(route('show', $secret));
})->with([
    'minutes' => ['minutes', '2026-01-01 12:01:00'],
    'hours' => ['hours', '2026-01-01 13:00:00'],
    'days' => ['days', '2026-01-02 12:00:00'],
    'weeks' => ['weeks', '2026-01-08 12:00:00'],
    'months' => ['months', '2026-02-01 12:00:00'],
    'years' => ['years', '2027-01-01 12:00:00'],
]);

test('rejects missing secret details', function () {
    $this->post(route('store'), [])
        ->assertSessionHasErrors(['content', 'expires_in', 'expires_unit', 'remaining_views']);

    $this->assertDatabaseCount('expiring_secrets', 0);
});

test('rejects invalid secret details', function (string $field, mixed $value) {
    $payload = ['content' => 'secret', 'expires_in' => 1, 'expires_unit' => 'hours', 'remaining_views' => 1];
    $payload[$field] = $value;

    $this->post(route('store'), $payload)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('expiring_secrets', 0);
})->with([
    'content must be text' => ['content', ['secret']],
    'expiry must be an integer' => ['expires_in', 'invalid'],
    'expiry unit must be supported' => ['expires_unit', 'decades'],
    'views must be an integer' => ['remaining_views', 'invalid'],
    'views cannot be negative' => ['remaining_views', -1],
]);

test('rejects expiry durations shorter than one unit', function (int $duration) {
    $this->post(route('store'), [
        'content' => 'secret',
        'expires_in' => $duration,
        'expires_unit' => 'hours',
        'remaining_views' => 1,
    ])->assertSessionHasErrors(['expires_in' => 'The expires in field must be at least 1.']);

    $this->assertDatabaseCount('expiring_secrets', 0);
})->with([0, -1]);

test('shows a secret once, escaping its content and consuming its remaining view', function () {
    $this->travelTo('2026-01-01 12:00:00');
    $secret = ExpiringSecret::factory()->create([
        'content' => '<script>alert(1)</script>',
        'expires_at' => '2026-01-01 13:00:00',
        'remaining_views' => 1,
    ]);

    $this->get(route('show', $secret))->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);

    expect($secret->fresh()->remaining_views)->toBe(0);
    $this->get(route('show', $secret))->assertNotFound();
});

test('does not reveal or consume an expired secret', function () {
    $this->travelTo('2026-01-01 12:00:00');
    $secret = ExpiringSecret::factory()->create([
        'content' => 'private message',
        'expires_at' => '2026-01-01 12:00:00',
        'remaining_views' => 1,
    ]);

    $this->get(route('show', $secret))->assertNotFound()->assertDontSee('private message');

    expect($secret->fresh()->remaining_views)->toBe(1);
});
