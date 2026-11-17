<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Laravel\Dusk\Browser;

uses(DatabaseMigrations::class);

test('the expiry estimate updates with the selected duration', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->waitForText('Estimated expiry:');

        $initialExpiry = $browser->text('[data-expiry-preview]');

        $browser->type('expires_in', '2')
            ->select('expires_unit', 'days')
            ->waitUntilMissingText($initialExpiry)
            ->assertSee('Estimated expiry:')
            ->type('expires_in', '0')
            ->assertDontSee('Estimated expiry:');
    });
});

test('a secret can be shared and viewed repeatedly before expiry', function () {
    Http::retry(20, 250)->get(config()->string('app.url'))->throw();

    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->type('content', 'A private message')
            ->press('Create')
            ->assertSee('Make another');

        $secretUrl = $browser->value('input[readonly]');

        if (! is_string($secretUrl)) {
            throw new RuntimeException('The secret URL was not found.');
        }

        $browser->visit($secretUrl)
            ->assertSee('A private message')
            ->refresh()
            ->assertSee('A private message');
    });
});
