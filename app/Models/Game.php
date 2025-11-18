<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'short_description',
        'long_description',
        'game_type',
        'min_players',
        'max_players',
        'estimated_duration_minutes',
        'difficulty',
        'game_data',
        'landing_page_data',
        'roles',
        'features',
        'price_tier',
        'price',
        'requires_code',
        'status',
        'is_featured',
        'ai_model_used',
        'ai_generated_at',
        'ai_generation_version',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'game_data' => 'array',
        'landing_page_data' => 'array',
        'roles' => 'array',
        'features' => 'array',
        'price' => 'decimal:2',
        'requires_code' => 'boolean',
        'is_featured' => 'boolean',
        'ai_generated_at' => 'datetime',
        'min_players' => 'integer',
        'max_players' => 'integer',
        'estimated_duration_minutes' => 'integer',
        'total_sessions' => 'integer',
        'total_players' => 'integer',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($game) {
            if (empty($game->slug)) {
                $game->slug = Str::slug($game->title);
            }
        });
    }

    /**
     * Creator of the game
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Game versions
     */
    public function versions()
    {
        return $this->hasMany(GameVersion::class);
    }

    /**
     * Active version
     */
    public function activeVersion()
    {
        return $this->hasOne(GameVersion::class)->where('is_active', true);
    }

    /**
     * Game assets (multimedia)
     */
    public function assets()
    {
        return $this->hasMany(GameAsset::class);
    }

    /**
     * Sessions of this game
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Licenses for this game
     */
    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    /**
     * Check if game is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if game is free
     */
    public function isFree(): bool
    {
        return $this->price_tier === 'free';
    }

    /**
     * Get active sessions count
     */
    public function getActiveSessionsCount(): int
    {
        return $this->sessions()
            ->whereIn('status', ['waiting', 'in_progress', 'paused'])
            ->count();
    }

    /**
     * Increment total sessions
     */
    public function incrementSessionCount(): void
    {
        $this->increment('total_sessions');
    }

    /**
     * Increment total players
     */
    public function incrementPlayerCount(int $count = 1): void
    {
        $this->increment('total_players', $count);
    }

    /**
     * Get game data with fallback to active version
     */
    public function getGameDataAttribute($value)
    {
        if ($value) {
            return json_decode($value, true);
        }

        // Fallback to active version
        $activeVersion = $this->activeVersion;
        return $activeVersion ? $activeVersion->game_data : null;
    }

    /**
     * Scope for published games
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for featured games
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for game type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('game_type', $type);
    }
}
