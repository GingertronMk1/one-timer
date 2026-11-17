<?php

namespace App\Models;

use Database\Factories\ExpiringSecretFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class ExpiringSecret extends Model
{
    /** @use HasFactory<ExpiringSecretFactory> */
    use HasFactory;

    use HasUuids;
    use Prunable;

    protected function casts(): array
    {
        return [
            'content' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }

    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now());
    }
}
