<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessRun extends Model
{
    protected $fillable = [
        'process_id',
        'started_by',
        'status',
        'current_step_id',
        'loop_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'loop_count' => 'integer',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'current_step_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RunResponse::class, 'run_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RunLog::class, 'run_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }
}
