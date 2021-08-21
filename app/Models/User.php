<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RequestLog::class);
    }

    public function save(array $options = array()): bool
    {
        if (isset($this->remember_token))
            unset($this->remember_token);

        return parent::save($options);
    }

    public function isUser(): bool
    {
        return $this->role_id === 1;
    }

    public function isHR(): bool
    {
        return $this->role_id === 2;
    }

    public function isManager(): bool
    {
        return $this->role_id === 3;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
