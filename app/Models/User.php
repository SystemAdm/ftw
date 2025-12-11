<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Qirolab\Laravel\Reactions\Contracts\ReactsInterface;
use Qirolab\Laravel\Reactions\Traits\Reacts;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements ReactsInterface
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, Reacts, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar',
        'birthday',
        'postal_code',
        'phone_public',
        'email_verified_at',
        'google_id',
        'discord_id',
        'ban_reason',
        'banned_by',
    ];

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
            'verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'banned_to' => 'datetime',
            'birthday' => 'date',
            'password' => 'hashed',
            'phone_public' => 'boolean',
        ];
    }

    /** @return BelongsToMany<PhoneNumber> */
    public function phoneNumbers(): BelongsToMany
    {
        return $this->belongsToMany(PhoneNumber::class)
            ->withPivot(['primary', 'verified_at', 'verified_by'])
            ->withTimestamps();
    }

    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class, 'postal_code', 'postal_code');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_user', 'minor_id', 'guardian_id')
            ->withPivot(['relationship', 'confirmed_guardian', 'confirmed_admin'])
            ->withTimestamps();
    }

    public function minors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_user', 'guardian_id', 'minor_id')
            ->withPivot(['relationship', 'confirmed_guardian', 'confirmed_admin'])
            ->withTimestamps();
    }

    /** @return HasMany<EventLog> */
    public function logs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }

    /** @return HasMany<UserBan> */
    public function bans(): HasMany
    {
        return $this->hasMany(UserBan::class);
    }

    public function isBanned(): bool
    {
        if (! $this->banned_at) {
            return false;
        }
        if ($this->banned_to === null) {
            return true;
        }

        return now()->lt($this->banned_to);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }
}
