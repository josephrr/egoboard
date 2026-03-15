<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'admin_token',
        'is_open',
        'allow_anonymous',
        'allow_reactions',
        'allow_one_note_per_participant',
        'theme',
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

    public function visibleNotes(): HasMany
    {
        return $this->notes()->where('is_visible', true);
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

    public function themeConfig(): array
    {
        return self::THEMES[$this->theme] ?? self::THEMES['sunrise'];
    }
}
