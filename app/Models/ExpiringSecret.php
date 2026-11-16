<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpiringSecret extends Model
{
    /** @use HasFactory<\Database\Factories\ExpiringSecretFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'content' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }
}
