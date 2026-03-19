<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public const TYPES = [
        'open' => 'Abierta',
        'single_choice' => 'Marcar con X',
        'true_false' => 'Verdadero / Falso',
    ];

    protected $fillable = [
        'room_id',
        'prompt',
        'question_type',
        'options',
        'position',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class)->latest();
    }

    public function resolvedOptions(): array
    {
        if ($this->question_type === 'true_false') {
            return ['Verdadero', 'Falso'];
        }

        return array_values(array_filter($this->options ?? [], fn ($option) => filled($option)));
    }
}
