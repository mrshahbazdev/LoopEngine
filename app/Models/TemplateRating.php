<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateRating extends Model
{
    protected $fillable = [
        'template_id',
        'user_id',
        'rating',
        'review',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProcessTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
