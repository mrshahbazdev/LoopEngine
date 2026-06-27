<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessStep extends Model
{
    protected $fillable = [
        'process_id',
        'order',
        'question_en',
        'question_de',
        'help_text_en',
        'help_text_de',
        'step_type',
        'is_loop_checkpoint',
        'is_required',
        'max_loops',
    ];

    protected function casts(): array
    {
        return [
            'is_loop_checkpoint' => 'boolean',
            'is_required' => 'boolean',
            'max_loops' => 'integer',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(StepOption::class, 'step_id')->orderBy('order');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(StepTransition::class, 'step_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RunResponse::class, 'step_id');
    }

    public function localizedQuestion(): string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->question_de ? $this->question_de : $this->question_en;
    }

    public function localizedHelpText(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->help_text_de ? $this->help_text_de : $this->help_text_en;
    }

    public function isLoopCheckpoint(): bool
    {
        return $this->is_loop_checkpoint;
    }
}
