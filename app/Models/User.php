<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'locale',
        'team',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeamLead(): bool
    {
        return $this->role === 'team_lead';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function canManageProcesses(): bool
    {
        return in_array($this->role, ['admin', 'team_lead']);
    }

    public function createdProcesses(): HasMany
    {
        return $this->hasMany(Process::class, 'created_by');
    }

    public function processRuns(): HasMany
    {
        return $this->hasMany(ProcessRun::class, 'started_by');
    }

    public function runLogs(): HasMany
    {
        return $this->hasMany(RunLog::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeamAssignment::class);
    }

    public function pendingAssignments(): HasMany
    {
        return $this->hasMany(TeamAssignment::class)->where('status', 'pending');
    }
}
