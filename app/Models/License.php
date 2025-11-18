<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class License extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'game_id',
        'type',
        'license_key',
        'status',
        'max_sessions',
        'sessions_used',
        'max_players',
        'valid_from',
        'valid_until',
        'payment_provider',
        'payment_id',
        'amount_paid',
        'currency',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'max_sessions' => 'integer',
        'sessions_used' => 'integer',
        'max_players' => 'integer',
        'amount_paid' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($license) {
            if (empty($license->license_key)) {
                $license->license_key = self::generateLicenseKey();
            }
        });
    }

    /**
     * Generate unique license key
     */
    protected static function generateLicenseKey(): string
    {
        do {
            $key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Check if license is valid
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Check validity period
        $now = now();
        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->isAfter($this->valid_until)) {
            return false;
        }

        // Check session usage
        if ($this->max_sessions !== null && $this->sessions_used >= $this->max_sessions) {
            return false;
        }

        return true;
    }

    /**
     * Use the license for a session
     */
    public function useForSession(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->increment('sessions_used');

        // Auto-consume if max sessions reached
        if ($this->max_sessions !== null && $this->sessions_used >= $this->max_sessions) {
            $this->update(['status' => 'consumed']);
        }

        return true;
    }

    /**
     * Revoke license
     */
    public function revoke(?string $reason = null): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['revoked_reason'] = $reason;
        $metadata['revoked_at'] = now();

        $this->update([
            'status' => 'revoked',
            'metadata' => $metadata,
        ]);
    }
}
