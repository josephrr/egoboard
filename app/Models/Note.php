<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'room_id',
        'author_name',
        'message',
        'color',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
