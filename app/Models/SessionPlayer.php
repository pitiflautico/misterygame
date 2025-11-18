<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'guest_name',
        'role_id',
        'role_name',
        'role_data',
        'secret_information',
        'status',
        'is_host',
        'player_state',
        'inventory',
        'notes',
        'actions_taken',
        'last_action_at',
        'joined_at',
    ];

    protected $casts = [
        'role_data' => 'array',
        'secret_information' => 'array',
        'player_state' => 'array',
        'inventory' => 'array',
        'notes' => 'array',
        'is_host' => 'boolean',
        'actions_taken' => 'integer',
        'last_action_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get player display name
     */
    public function getDisplayName(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Unknown Player';
    }

    /**
     * Check if player is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark as joined
     */
    public function markAsJoined(): void
    {
        $this->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    /**
     * Increment action count
     */
    public function recordAction(): void
    {
        $this->increment('actions_taken');
        $this->update(['last_action_at' => now()]);
    }

    /**
     * Add item to inventory
     */
    public function addToInventory(string $itemId, array $itemData): void
    {
        $inventory = $this->inventory ?? [];
        $inventory[$itemId] = $itemData;
        $this->update(['inventory' => $inventory]);
    }

    /**
     * Add note
     */
    public function addNote(string $note): void
    {
        $notes = $this->notes ?? [];
        $notes[] = [
            'content' => $note,
            'timestamp' => now(),
        ];
        $this->update(['notes' => $notes]);
    }
}
