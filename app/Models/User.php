<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'hospcode',
        'position',
        'cid',
        'provider_id',
        'moph_token',
        'moph_token_expire',
        'active',
        'allow_death',
        'allow_birth',
        'allow_death_dashboard',
        'allow_birth_dashboard',
    ];

    /**
     * Relationship with Hospital model.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospcode', 'hospcode');
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->active === 'Y' || $this->active === '1' || $this->active === true;
    }

    /**
     * Check if user is linked to Provider ID.
     */
    public function hasProviderId(): bool
    {
        return !empty($this->provider_id);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user has full death data access permission.
     *
     * @return bool
     */
    public function canAccessDeath(): bool
    {
        return $this->isAdmin() || (bool)$this->allow_death;
    }

    /**
     * Check if the user has death dashboard access permission.
     *
     * @return bool
     */
    public function canAccessDeathDashboard(): bool
    {
        return $this->canAccessDeath() || (bool)$this->allow_death_dashboard;
    }

    /**
     * Check if the user has full birth data access permission.
     *
     * @return bool
     */
    public function canAccessBirth(): bool
    {
        return $this->isAdmin() || (bool)$this->allow_birth;
    }

    /**
     * Check if the user has birth dashboard access permission.
     *
     * @return bool
     */
    public function canAccessBirthDashboard(): bool
    {
        return $this->canAccessBirth() || (bool)$this->allow_birth_dashboard;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'moph_token_expire' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
