<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $fillable = [
        'user_id',
        'permission',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public const AVAILABLE = [
        'processes.create' => 'Create processes',
        'processes.edit' => 'Edit processes',
        'processes.delete' => 'Delete processes',
        'processes.activate' => 'Activate/archive processes',
        'processes.duplicate' => 'Duplicate processes',
        'processes.version' => 'Create process versions',
        'runs.start' => 'Start process runs',
        'runs.export' => 'Export run data',
        'team.assign' => 'Assign processes to team',
        'team.view' => 'View team dashboard',
        'templates.share' => 'Share templates to marketplace',
        'templates.install' => 'Install templates from marketplace',
        'webhooks.manage' => 'Manage webhooks',
        'admin.analytics' => 'View analytics',
        'admin.audit' => 'View audit log',
        'admin.users' => 'Manage users',
        'admin.permissions' => 'Manage permissions',
    ];
}
