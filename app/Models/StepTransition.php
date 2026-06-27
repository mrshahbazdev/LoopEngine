<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepTransition extends Model
{
    protected $fillable = [
        'step_id',
        'option_id',
        'action_type',
        'target_step_id',
        'target_process_id',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'step_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(StepOption::class, 'option_id');
    }

    public function targetStep(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'target_step_id');
    }

    public function targetProcess(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'target_process_id');
    }
}
