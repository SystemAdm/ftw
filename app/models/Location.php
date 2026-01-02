<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'postal_code',
        'name',
        'active',
        'description',
        'latitude',
        'longitude',
        'google_maps_url',
        'images',
        'street_address',
        'street_number',
        'link',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /**
     * The postal code this location belongs to.
     */
    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class, 'postal_code', 'postal_code');
    }

    /**
     * Events taking place at this location.
     *
     * @return HasMany<Event>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Combined postal code and city string for display.
     */
    public function postal(): Attribute
    {
        return Attribute::get(function (): string {
            $code = (string) $this->postal_code;
            $city = $this->postalCode?->city;

            return trim($code.' '.($city ?? ''));
        });
    }
}
