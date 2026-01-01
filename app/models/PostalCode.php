<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostalCode extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'postal_code';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'postal_code',
        'city',
        'state',
        'country',
        'municipality',
    ];

    public function getRouteKeyName(): string
    {
        return 'postal_code';
    }

    /**
     * Users belonging to this postal code.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'postal_code', 'postal_code');
    }
}
