<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'role',
        'is_premium',
        'premium_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_premium' => 'boolean',
        'premium_until' => 'datetime',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is creator
     */
    public function isCreator(): bool
    {
        return in_array($this->role, ['creator', 'admin']);
    }

    /**
     * Check if user has premium access
     */
    public function hasPremiumAccess(): bool
    {
        if (!$this->is_premium) {
            return false;
        }

        if ($this->premium_until === null) {
            return true; // Lifetime premium
        }

        return $this->premium_until->isFuture();
    }

    /**
     * Games created by this user
     */
    public function createdGames()
    {
        return $this->hasMany(Game::class, 'created_by');
    }

    /**
     * Sessions hosted by this user
     */
    public function hostedSessions()
    {
        return $this->hasMany(Session::class, 'host_user_id');
    }

    /**
     * Session participations
     */
    public function sessionParticipations()
    {
        return $this->hasMany(SessionPlayer::class);
    }

    /**
     * User's licenses
     */
    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    /**
     * User's purchases
     */
    public function purchases()
    {
        return $this->hasMany(PurchaseLog::class);
    }

    /**
     * Admin actions performed by this user
     */
    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class);
    }

    /**
     * Check if user has access to a specific game
     */
    public function hasAccessToGame(Game $game): bool
    {
        // Free games are accessible to everyone
        if ($game->price_tier === 'free') {
            return true;
        }

        // Check if user has an active license for this game
        return $this->licenses()
            ->where('game_id', $game->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->exists();
    }
}
