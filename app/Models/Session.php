<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_code',
        'game_id',
        'host_user_id',
        'max_players',
        'mode',
        'is_public',
        'status',
        'current_scene_id',
        'game_state',
        'flags',
        'discovered_clues',
        'player_decisions',
        'started_at',
        'completed_at',
        'last_activity_at',
        'total_play_time_seconds',
        'final_results',
        'mystery_solved',
        'score',
    ];

    protected $casts = [
        'game_state' => 'array',
        'flags' => 'array',
        'discovered_clues' => 'array',
        'player_decisions' => 'array',
        'final_results' => 'array',
        'is_public' => 'boolean',
        'mystery_solved' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'max_players' => 'integer',
        'total_play_time_seconds' => 'integer',
        'score' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->session_code)) {
                $session->session_code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate unique 6-digit session code
     */
    protected static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('session_code', $code)->exists());

        return $code;
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function players()
    {
        return $this->hasMany(SessionPlayer::class);
    }

    public function events()
    {
        return $this->hasMany(SessionEvent::class);
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['waiting', 'in_progress']);
    }

    /**
     * Check if session is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if session can accept more players
     */
    public function canAcceptPlayers(): bool
    {
        return $this->players()->count() < $this->max_players && $this->isActive();
    }

    /**
     * Start the session
     */
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Complete the session
     */
    public function complete(array $results = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_results' => $results,
        ]);
    }

    /**
     * Update activity timestamp
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Add a flag
     */
    public function setFlag(string $key, $value): void
    {
        $flags = $this->flags ?? [];
        $flags[$key] = $value;
        $this->update(['flags' => $flags]);
    }

    /**
     * Get a flag value
     */
    public function getFlag(string $key, $default = null)
    {
        return $this->flags[$key] ?? $default;
    }

    /**
     * Add discovered clue
     */
    public function addClue(string $clueId, array $clueData): void
    {
        $clues = $this->discovered_clues ?? [];
        $clues[$clueId] = array_merge($clueData, ['discovered_at' => now()]);
        $this->update(['discovered_clues' => $clues]);
    }

    /**
     * Record player decision
     */
    public function recordDecision(string $sceneId, string $actionId, $userId): void
    {
        $decisions = $this->player_decisions ?? [];
        $decisions[] = [
            'scene_id' => $sceneId,
            'action_id' => $actionId,
            'user_id' => $userId,
            'timestamp' => now(),
        ];
        $this->update(['player_decisions' => $decisions]);
    }
}
