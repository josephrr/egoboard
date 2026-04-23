<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Room extends Model
{
    public const TYPES = [
        'notes' => 'Muro de notas',
        'questions' => 'Sala de preguntas',
        'canvas' => 'Sala de dibujo',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'admin_token',
        'room_type',
        'is_open',
        'allow_anonymous',
        'allow_reactions',
        'allow_one_note_per_participant',
        'theme',
        'background_image_path',
        'closes_at',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'allow_anonymous' => 'boolean',
        'allow_reactions' => 'boolean',
        'allow_one_note_per_participant' => 'boolean',
        'closes_at' => 'datetime',
    ];

    public const THEMES = [
        'sunrise' => [
            'name' => 'Sunrise',
            'badge' => 'bg-orange-100 text-orange-700',
            'hero' => 'from-orange-100 via-amber-50 to-white',
        ],
        'ocean' => [
            'name' => 'Ocean',
            'badge' => 'bg-sky-100 text-sky-700',
            'hero' => 'from-sky-100 via-cyan-50 to-white',
        ],
        'forest' => [
            'name' => 'Forest',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'hero' => 'from-emerald-100 via-lime-50 to-white',
        ],
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function visibleNotes(): HasMany
    {
        return $this->notes()->where('is_visible', true);
    }

    public function canvasDrawings(): HasMany
    {
        return $this->hasMany(CanvasDrawing::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_open', true)
            ->where(function (Builder $query) {
                $query->whereNull('closes_at')
                    ->orWhere('closes_at', '>', now());
            });
    }

    public function isClosed(): bool
    {
        return ! $this->is_open || ($this->closes_at instanceof CarbonInterface && $this->closes_at->isPast());
    }

    public function isQuestionRoom(): bool
    {
        return $this->room_type === 'questions';
    }

    public function isCanvasRoom(): bool
    {
        return $this->room_type === 'canvas';
    }

    public function isNoteRoom(): bool
    {
        return ! $this->isQuestionRoom() && ! $this->isCanvasRoom();
    }

    public function backgroundImageUrl(): ?string
    {
        if (! $this->background_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->background_image_path);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->room_type] ?? self::TYPES['notes'];
    }

    public function themeConfig(): array
    {
        return self::THEMES[$this->theme] ?? self::THEMES['sunrise'];
    }
}
