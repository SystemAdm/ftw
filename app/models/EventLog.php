<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'action',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Event related to this log.
     */
    public function event(): BelongsTo
    {
        // Referencing the expected Event model without importing keeps intent clear
        return $this->belongsTo(Event::class);
    }

    /**
     * User related to this log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
