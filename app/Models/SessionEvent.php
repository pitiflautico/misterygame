<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'event_type',
        'scene_id',
        'action_id',
        'event_data',
        'description',
        'success',
        'result_message',
        'created_at',
    ];

    protected $casts = [
        'event_data' => 'array',
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->created_at = now();
        });
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new event
     */
    public static function log(
        int $sessionId,
        string $eventType,
        ?int $userId = null,
        ?string $sceneId = null,
        ?string $actionId = null,
        array $eventData = [],
        ?string $description = null,
        bool $success = true,
        ?string $resultMessage = null
    ): self {
        return self::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'scene_id' => $sceneId,
            'action_id' => $actionId,
            'event_data' => $eventData,
            'description' => $description,
            'success' => $success,
            'result_message' => $resultMessage,
        ]);
    }
}
