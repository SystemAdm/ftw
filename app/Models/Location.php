<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes, HasFactory;

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
}
