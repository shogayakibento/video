<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tweet extends Model
{
    protected $fillable = [
        'video_id',
        'tweet_id',
        'tweet_text',
        'author_username',
        'like_count',
        'retweet_count',
        'tweeted_at',
    ];

    protected $casts = [
        'like_count' => 'integer',
        'retweet_count' => 'integer',
        'tweeted_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
