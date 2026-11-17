<?php

use App\Models\ExpiringSecret;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

test('emails each recipient a unique secret', function () {
    $this->travelTo('2026-01-01 12:00:00');
    $response = $this->post(route('store'), [
        'content' => 'private message',
        'expires_in' => 1,
        'expires_unit' => 'hours',
        'recipients' => " first@example.com \r\n\r\nsecond@example.com",
    ])->assertOk();

    $secrets = ExpiringSecret::query()->get();
    expect($secrets)->toHaveCount(2);
    $recipientSecrets = $secrets->values();
    $response->assertSee('Secret sent to 2 users')->assertDontSee('<input', false);
    $messages = Mail::mailer()->getSymfonyTransport()->messages();
    expect($messages)->toHaveCount(2);
    expect($messages->map(fn ($message) => $message->getOriginalMessage()->getTo()[0]->getAddress())->all())
        ->toBe(['first@example.com', 'second@example.com']);
    foreach ($messages as $message) {
        expect($message->getOriginalMessage()->getHtmlBody())
            ->not->toContain('private message');
    }
    foreach ($recipientSecrets as $secret) {
        expect($messages->filter(fn ($message) => str_contains($message->getOriginalMessage()->getHtmlBody(), route('show', $secret))))->toHaveCount(1);
        expect($secret->content)->toBe('private message');
        expect($secret->expires_at->format('Y-m-d H:i:s'))->toBe('2026-01-01 13:00:00');
    }

    $this->get(route('show', $recipientSecrets[0]))->assertOk();
    $this->get(route('show', $recipientSecrets[0]))->assertOk();
    $this->get(route('show', $recipientSecrets[1]))->assertOk();
});

test('confirms delivery to one user', function () {
    $this->post(route('store'), [
        'content' => 'private message',
        'expires_in' => 1,
        'expires_unit' => 'hours',
        'recipients' => 'person@example.com',
    ])->assertOk()->assertSee('Secret sent to 1 user')->assertDontSee('<input', false);

    $this->assertDatabaseCount('expiring_secrets', 1);
});

test('shows a shareable link when the recipient field contains only blank lines', function () {
    $response = $this->post(route('store'), [
        'content' => 'private message',
        'expires_in' => 1,
        'expires_unit' => 'hours',
        'recipients' => " \n ",
    ])->assertOk()->assertDontSee('Secret sent to');

    $response->assertSee(route('show', ExpiringSecret::query()->sole()));
});

test('rejects invalid recipient addresses before creating a secret', function () {
    $this->post(route('store'), [
        'content' => 'private message',
        'expires_in' => 1,
        'expires_unit' => 'hours',
        'recipients' => "valid@example.com\ninvalid address",
    ])->assertSessionHasErrors('recipients');

    $this->assertDatabaseCount('expiring_secrets', 0);
    expect(Mail::mailer()->getSymfonyTransport()->messages())->toBeEmpty();
});

test('creates an encrypted secret with the requested expiry', function (string $unit, string $expectedExpiry) {
    $this->travelTo('2026-01-01 12:00:00');

    $response = $this->post(route('store'), [
        'content' => 'private message',
        'expires_in' => 1,
        'expires_unit' => $unit,
    ]);

    $response->assertOk()->assertViewIs('expiringSecret.store');
    $secret = ExpiringSecret::query()->sole();
    expect($secret->content)->toBe('private message');
    expect($secret->expires_at->format('Y-m-d H:i:s'))->toBe($expectedExpiry);
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
        ->assertSessionHasErrors(['content', 'expires_in', 'expires_unit']);

    $this->assertDatabaseCount('expiring_secrets', 0);
});

test('rejects invalid secret details', function (string $field, mixed $value) {
    $payload = ['content' => 'secret', 'expires_in' => 1, 'expires_unit' => 'hours'];
    $payload[$field] = $value;

    $this->post(route('store'), $payload)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('expiring_secrets', 0);
})->with([
    'content must be text' => ['content', ['secret']],
    'expiry must be an integer' => ['expires_in', 'invalid'],
    'expiry unit must be supported' => ['expires_unit', 'decades'],
]);

test('rejects expiry durations shorter than one unit', function (int $duration) {
    $this->post(route('store'), [
        'content' => 'secret',
        'expires_in' => $duration,
        'expires_unit' => 'hours',
    ])->assertSessionHasErrors(['expires_in' => 'The expires in field must be at least 1.']);

    $this->assertDatabaseCount('expiring_secrets', 0);
})->with([0, -1]);

test('shows a secret repeatedly before expiry and escapes its content', function () {
    $this->travelTo('2026-01-01 12:00:00');
    $secret = ExpiringSecret::factory()->create([
        'content' => '<script>alert(1)</script>',
        'expires_at' => '2026-01-01 13:00:00',
    ]);

    $this->get(route('show', $secret))->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);

    $this->get(route('show', $secret))->assertOk();
});

test('deletes an expired secret without revealing it', function () {
    $this->travelTo('2026-01-01 12:00:00');
    $secret = ExpiringSecret::factory()->create([
        'content' => 'private message',
        'expires_at' => '2026-01-01 12:00:00',
    ]);

    $this->get(route('show', $secret))->assertNotFound()->assertDontSee('private message');

    $this->assertModelMissing($secret);
});

test('prunes expired secrets while retaining unexpired secrets', function () {
    $this->travelTo('2026-01-01 12:00:00');
    $expired = ExpiringSecret::factory()->create(['content' => 'expired', 'expires_at' => '2026-01-01 11:59:59']);
    $atExpiry = ExpiringSecret::factory()->create(['content' => 'at expiry', 'expires_at' => '2026-01-01 12:00:00']);
    $active = ExpiringSecret::factory()->create(['content' => 'active', 'expires_at' => '2026-01-01 12:00:01']);

    $this->artisan('model:prune', ['--model' => [ExpiringSecret::class]])->assertSuccessful();

    $this->assertModelMissing($expired);
    $this->assertModelMissing($atExpiry);
    $this->assertModelExists($active);
});
