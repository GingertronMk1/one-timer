<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Laravel\Dusk\Browser;

uses(DatabaseMigrations::class);

test('a secret can be shared and viewed repeatedly before expiry', function () {
    Http::retry(20, 250)->get(config('app.url'))->throw();

    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->type('content', 'A private message')
            ->press('Create')
            ->assertSee('Make another');

        $secretUrl = $browser->value('input[readonly]');

        $browser->visit($secretUrl)
            ->assertSee('A private message')
            ->refresh()
            ->assertSee('A private message');
    });
});
