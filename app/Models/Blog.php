<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Qirolab\Laravel\Reactions\Contracts\ReactableInterface;
use Qirolab\Laravel\Reactions\Traits\Reactable;
use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model implements ReactableInterface
{
    use HasFactory;
    use HasTags;
    use Reactable;
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
    protected $appends = ['published'];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getPublishedAttribute()
    {
        return $this->published_at ? $this->published_at->diffForHumans() : null;
    }
}
