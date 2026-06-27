<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StepOption extends Model
{
    protected $fillable = [
        'step_id',
        'label_en',
        'label_de',
        'value',
        'order',
        'color',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'step_id');
    }

    public function transition(): HasOne
    {
        return $this->hasOne(StepTransition::class, 'option_id');
    }

    public function localizedLabel(): string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->label_de ? $this->label_de : $this->label_en;
    }
}
