<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteVote extends Model
{
    protected $fillable = [
        'note_id',
        'participant_key',
        'reaction',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
