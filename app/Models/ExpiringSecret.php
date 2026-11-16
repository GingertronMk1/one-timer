<?php

namespace App\Models;

use Database\Factories\ExpiringSecretFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpiringSecret extends Model
{
    /** @use HasFactory<ExpiringSecretFactory> */
    use HasFactory;

    use HasUuids;

    protected function casts(): array
    {
        return [
            'content' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }

    public function removeView(): bool
    {
        $this->remaining_views--;

        return $this->save();
    }

    public function canView(): bool
    {
        return $this->remaining_views > 0 && $this->expires_at > now();
    }
}
