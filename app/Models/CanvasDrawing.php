<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanvasDrawing extends Model
{
    protected $fillable = [
        'room_id',
        'author_name',
        'participant_key',
        'canvas_data',
        'preview_png',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
