<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Resources\JsonApi\RelationResolver;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    
    /**
     * The attributes that should be hidden for serialization.
    *
    * @var list<string>
    */
    
    /**
     * Get the attributes that should be cast.
    *
    * @return array<string, string>
    */
    protected $fillable = [
        'name',
        'pseudo',
        'email',
        'password',
        'telephone',
        'role',
        'account_status',
        'profile_photo',
        'access_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'access_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function invitations(){
        return $this->hasMany(Invitation::class, 'player_id');
    }

    public function isAdmin():bool{
        return $this->role==='admin';
    }
}
