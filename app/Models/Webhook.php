<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    protected $fillable = [
        'created_by',
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'last_triggered_at',
        'failure_count',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }
}
