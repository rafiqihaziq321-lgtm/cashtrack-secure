<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role_id', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            $wasOwner = Role::find($user->getOriginal('role_id'))?->name === 'Owner';
            if ($wasOwner && ($user->isDirty('role_id') || ! $user->is_active)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['account' => 'Akaun Owner dilindungi daripada penukaran peranan atau nyahaktif.']);
            }
        });
        static::deleting(function (User $user): void {
            if ($user->role_id !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages(['account' => 'Akaun operasi dikekalkan untuk sejarah audit.']);
            }
        });
        static::updated(function (User $user): void {
            $fields = array_intersect(['name', 'email', 'is_active', 'role_id'], array_keys($user->getChanges()));
            if ($fields || $user->wasChanged('password')) {
                app(\App\Services\InventoryService::class)->audit(auth()->id() ?? $user->id, 'profil.dikemas_kini', 'user', $user->id,
                    array_intersect_key($user->getOriginal(), array_flip($fields)),
                    $user->only($fields) + ['password_changed' => $user->wasChanged('password')]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}