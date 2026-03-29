<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FanzaViewLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'content_id',
        'session_id',
    ];
}
