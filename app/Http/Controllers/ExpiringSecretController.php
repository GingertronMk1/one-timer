<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpiringSecretRequest;
use App\Models\ExpiringSecret;
use App\TimeUnit;

class ExpiringSecretController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expiringSecret.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpiringSecretRequest $request)
    {
        $validated = $request->validated();
        $expiresIn = $validated['expires_in'];
        $expiry = match ($validated['expires_unit']) {
            TimeUnit::MINUTES => now()->addMinutes($expiresIn),
            TimeUnit::HOURS => now()->addHours($expiresIn),
            TimeUnit::DAYS => now()->addDays($expiresIn),
            TimeUnit::WEEKS => now()->addWeeks($expiresIn),
            TimeUnit::MONTHS => now()->addMonths($expiresIn),
            TimeUnit::YEARS => now()->addYears($expiresIn),
        };
        $expiringSecret = new ExpiringSecret();
        $expiringSecret->expires_at = $expiry;
        $expiringSecret->content = $validated['content'];
        $expiringSecret->remaining_views = $validated['remaining_views'];
        if (!$expiringSecret->save()) {
            throw new \Exception('There was an error saving this Expiring Secret. Please try again later.');
        }
        return view('expiringSecret.store', [
            'expiringSecret' => $expiringSecret,
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(ExpiringSecret $expiringSecret)
    {
        //
    }
}
