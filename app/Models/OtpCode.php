<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'destinataire',
        'canal',
        'code',
        'tentatives',
        'expires_at',
    ];

    protected function casts():array{
        return [
            'expires_at' => 'datetime',
        ];
    }

    // protected $casts = [
    //     'expires_at' => 'datetime',
    // ];

    // Est-ce que ce code est expiré ?
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    // Est-ce que le nombre de tentatives est dépassé ?
    public function isBloque(): bool
    {
        return $this->tentatives >= 3;
    }
}