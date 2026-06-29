<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessTemplate extends Model
{
    protected $fillable = [
        'process_id',
        'shared_by',
        'name_en',
        'name_de',
        'description_en',
        'description_de',
        'category',
        'tags',
        'is_public',
        'install_count',
        'rating',
        'rating_count',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_public' => 'boolean',
            'rating' => 'decimal:2',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(TemplateRating::class, 'template_id');
    }

    public function localizedName(): string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->name_de ? $this->name_de : $this->name_en;
    }

    public function localizedDescription(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->description_de ? $this->description_de : $this->description_en;
    }

    public function recalculateRating(): void
    {
        $this->rating = $this->ratings()->avg('rating') ?? 0;
        $this->rating_count = $this->ratings()->count();
        $this->save();
    }
}
