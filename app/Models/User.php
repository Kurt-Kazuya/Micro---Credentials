<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'username',
        'password',
        'role_id',
        'student_id',
        'phone',
        'location',
        'avatar_url',
        'is_active',
        'user_code',
        'date_of_birth',
        'gender',
        'education',
        'about',
        'bio',
        'language',
        'timezone',
        'profile_completed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')->withTimestamps();
    }

    public function competencyProgresses(): HasMany
    {
        return $this->hasMany(CompetencyProgress::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
