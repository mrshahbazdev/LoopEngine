<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'success',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'success' => 'boolean',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
