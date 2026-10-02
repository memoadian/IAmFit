<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /** Solo los administradores entran al panel Filament. */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function bodyWeightEntries(): HasMany
    {
        return $this->hasMany(BodyWeightEntry::class);
    }

    public function foodLogEntries(): HasMany
    {
        return $this->hasMany(FoodLogEntry::class);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }

    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    /** Peso más reciente registrado en kg, o null si nunca registró. */
    public function latestWeightKg(): ?float
    {
        $value = $this->bodyWeightEntries()
            ->orderByDesc('measured_on')
            ->value('weight_kg');

        return $value === null ? null : (float) $value;
    }
}
