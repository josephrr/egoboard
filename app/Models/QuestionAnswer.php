<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAnswer extends Model
{
    protected $fillable = [
        'room_id',
        'question_id',
        'author_name',
        'participant_key',
        'answer_text',
        'selected_option',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function displayAnswer(): string
    {
        return $this->selected_option ?: $this->answer_text;
    }
}
