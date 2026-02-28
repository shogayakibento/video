<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'dmm_content_id',
        'title',
        'actress',
        'thumbnail_url',
        'affiliate_url',
        'genre',
        'maker',
        'release_date',
        'total_likes',
        'total_retweets',
        'weekly_likes',
        'click_count',
    ];

    protected $casts = [
        'release_date' => 'date',
        'total_likes' => 'integer',
        'total_retweets' => 'integer',
        'weekly_likes' => 'integer',
        'click_count' => 'integer',
    ];

    public function tweets(): HasMany
    {
        return $this->hasMany(Tweet::class);
    }

    public function clickLogs(): HasMany
    {
        return $this->hasMany(ClickLog::class);
    }

    public function recalculateEngagement(): void
    {
        $this->update([
            'total_likes'    => $this->tweets()->sum('like_count'),
            'total_retweets' => $this->tweets()->sum('retweet_count'),
            'weekly_likes'   => $this->tweets()
                ->whereBetween('tweeted_at', [now()->subDays(7), now()->subHours(24)])
                ->sum('like_count'),
        ]);
    }
}
