<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    public const CATEGORIES = [
        'problema' => 'Problema',
        'idea' => 'Idea',
        'queja' => 'Queja',
        'oportunidad' => 'Oportunidad',
    ];

    public const REACTIONS = [
        'me_pasa' => 'Me pasa',
        'importante' => 'Importante',
        'quiero_resolverlo' => 'Quiero resolverlo',
    ];

    protected $fillable = [
        'room_id',
        'author_name',
        'message',
        'color',
        'participant_key',
        'category',
        'is_anonymous',
        'is_visible',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(NoteVote::class);
    }

    public function displayName(): string
    {
        return $this->is_anonymous ? 'Anonimo' : $this->author_name;
    }
}
