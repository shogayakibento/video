<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MgsVideo extends Model
{
    protected $fillable = [
        'product_code',
        'title',
        'actress',
        'thumbnail_url',
        'sample_video_url',
        'affiliate_url',
        'genre',
        'maker',
        'release_date',
        'review_score',
        'review_count',
        'mgs_rank',
    ];

    protected $casts = [
        'release_date' => 'date',
        'review_score' => 'float',
        'review_count' => 'integer',
        'mgs_rank'     => 'integer',
    ];
}
