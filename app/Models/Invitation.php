<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'environment_id',
        'canal',
        'destinataire',
        'player_id',
        'token',
        'statut',
        'expires_at',
        'used_at',
    ];
    
    ///une manière avec laravel en dessus de 11
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime'
        ];
    }
///une manière avec laravel en dessous de 11
    // protected $casts = [
    // 'expires_at'=>'datetime',
    // 'used_at'=>'datetime'
    // ];

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->statut === 'expired';
    }

    public function isUsed(): bool
    {
        return $this->statut === 'used';
    }
}
