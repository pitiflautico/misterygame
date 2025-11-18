<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'version_number',
        'game_data',
        'changelog',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'game_data' => 'array',
        'changelog' => 'array',
        'is_active' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Activate this version
     */
    public function activate(): void
    {
        // Deactivate all other versions
        $this->game->versions()->update(['is_active' => false]);

        // Activate this version
        $this->update(['is_active' => true]);
    }
}
