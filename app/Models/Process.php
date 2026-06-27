<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    protected $fillable = [
        'name_en',
        'name_de',
        'description_en',
        'description_de',
        'created_by',
        'status',
        'version',
        'category',
        'icon',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class)->orderBy('order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ProcessRun::class);
    }

    public function firstStep(): ?ProcessStep
    {
        return $this->steps()->orderBy('order')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
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
}
