<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunResponse extends Model
{
    protected $fillable = [
        'run_id',
        'step_id',
        'option_id',
        'response_text',
        'responded_by',
        'loop_iteration',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'loop_iteration' => 'integer',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ProcessRun::class, 'run_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'step_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(StepOption::class, 'option_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
