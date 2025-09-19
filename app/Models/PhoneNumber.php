<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhoneNumber extends Model
{
    protected $fillable = ['e164', 'raw'];

    /** @return BelongsToMany<User> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['primary', 'verified_at', 'verified_by'])
            ->withTimestamps();
    }
}
