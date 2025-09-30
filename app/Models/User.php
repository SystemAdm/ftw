<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Qirolab\Laravel\Reactions\Contracts\ReactsInterface;
use Spatie\Permission\Traits\HasRoles;
use Qirolab\Laravel\Reactions\Traits\Reacts;

class User extends Authenticatable implements ReactsInterface
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, Reacts ;

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
        'phone_public',
        'email_verified_at',
        'google_id',
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

    public function postalCode() : BelongsTo
    {
        return $this->belongsTo(PostalCode::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(User::class, 'guardian_user', 'minor_id', 'guardian_id')
            ->withPivot(['relationship'])
            ->withTimestamps();
    }

    public function minors() {
        return $this->belongsToMany(User::class, 'guardian_user', 'guardian_id', 'minor_id')
            ->withPivot(['relationship'])
            ->withTimestamps();
    }
}
