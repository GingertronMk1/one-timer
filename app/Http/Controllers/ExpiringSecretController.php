<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpiringSecretRequest;
use App\Models\ExpiringSecret;
use App\TimeUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class ExpiringSecretController extends Controller
{
    public function create(): View
    {
        return view('expiringSecret.create');
    }

    public function store(StoreExpiringSecretRequest $request): View
    {
        $expiresIn = $request->integer('expires_in');
        $content = $request->string('content')->toString();
        $expiry = match (TimeUnit::from($request->string('expires_unit')->toString())) {
            TimeUnit::MINUTES => now()->addMinutes($expiresIn),
            TimeUnit::HOURS => now()->addHours($expiresIn),
            TimeUnit::DAYS => now()->addDays($expiresIn),
            TimeUnit::WEEKS => now()->addWeeks($expiresIn),
            TimeUnit::MONTHS => now()->addMonths($expiresIn),
            TimeUnit::YEARS => now()->addYears($expiresIn),
        };
        $expiringSecret = new ExpiringSecret;
        $expiringSecret->expires_at = $expiry;
        $expiringSecret->content = $content;
        $recipients = $request->recipients();

        if ($recipients === [] && ! $expiringSecret->save()) {
            throw new \Exception('There was an error saving this Expiring Secret. Please try again later.');
        }

        foreach ($recipients as $recipient) {
            $recipientSecret = $expiringSecret->replicate();
            $recipientSecret->content = $content;
            if (! $recipientSecret->save()) {
                throw new \Exception('There was an error saving this Expiring Secret. Please try again later.');
            }

            Mail::send('emails.expiring-secret', ['expiringSecret' => $recipientSecret], function (Message $message) use ($recipient): void {
                $message->to($recipient)->subject('You have received a secret');
            });
        }

        return view('expiringSecret.store', [
            'expiringSecret' => $expiringSecret,
            'recipientCount' => count($recipients),
        ]);

    }

    public function show(ExpiringSecret $expiringSecret): View
    {
        if ($expiringSecret->expires_at <= now()) {
            $expiringSecret->delete();

            abort(404);
        }

        return view('expiringSecret.show', ['expiringSecret' => $expiringSecret]);
    }
}
