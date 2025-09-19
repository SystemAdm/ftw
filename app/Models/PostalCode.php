<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostalCode extends Model
{
    use SoftDeletes, HasFactory;
    protected $primaryKey = 'postal_code';
    protected $fillable = [
        'postal_code',
        'city',
        'state',
        'country',
        'county',
    ];
}
