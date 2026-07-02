<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'name_en',
        'name_de',
        'description_en',
        'description_de',
        'created_by',
        'company_id',
        'status',
        'version',
        'category',
        'icon',
        'parent_id',
        'is_latest_version',
    ];

    protected function casts(): array
    {
        return [
            'is_latest_version' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class)->orderBy('order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ProcessRun::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeamAssignment::class);
    }

    public function template()
    {
        return $this->hasOne(ProcessTemplate::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function firstStep(): ?ProcessStep
    {
        return $this->steps()->orderBy('order')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function localizedName(): string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->name_de ? $this->name_de : $this->name_en;
    }

    public function localizedDescription(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'de' && $this->description_de ? $this->description_de : $this->description_en;
    }

    public function duplicate(User $user): self
    {
        $clone = $this->replicate(['status', 'version']);
        $clone->status = 'draft';
        $clone->version = 1;
        $clone->created_by = $user->id;
        $clone->name_en = $this->name_en . ' (Copy)';
        $clone->name_de = $this->name_de ? $this->name_de . ' (Kopie)' : null;
        $clone->save();

        foreach ($this->steps as $step) {
            $newStep = $step->replicate();
            $newStep->process_id = $clone->id;
            $newStep->save();

            $optionMap = [];
            foreach ($step->options as $option) {
                $newOption = $option->replicate();
                $newOption->step_id = $newStep->id;
                $newOption->save();
                $optionMap[$option->id] = $newOption->id;
            }
        }

        // Clone transitions (second pass to resolve step/option IDs)
        $stepMap = [];
        $origSteps = $this->steps()->get();
        $cloneSteps = $clone->steps()->get();
        foreach ($origSteps as $i => $origStep) {
            $stepMap[$origStep->id] = $cloneSteps[$i]->id;
        }

        foreach ($origSteps as $i => $origStep) {
            foreach ($origStep->transitions as $transition) {
                $cloneSteps[$i]->transitions()->create([
                    'option_id' => $transition->option_id ? ($this->findClonedOptionId($origStep, $cloneSteps[$i], $transition->option_id)) : null,
                    'action_type' => $transition->action_type,
                    'target_step_id' => $transition->target_step_id ? ($stepMap[$transition->target_step_id] ?? null) : null,
                    'target_process_id' => $transition->target_process_id,
                ]);
            }
        }

        return $clone;
    }

    protected function findClonedOptionId(ProcessStep $origStep, ProcessStep $cloneStep, int $origOptionId): ?int
    {
        $origOptions = $origStep->options()->get();
        $cloneOptions = $cloneStep->options()->get();

        foreach ($origOptions as $i => $origOption) {
            if ($origOption->id === $origOptionId && isset($cloneOptions[$i])) {
                return $cloneOptions[$i]->id;
            }
        }

        return null;
    }

    public function createNewVersion(User $user): self
    {
        $parentId = $this->parent_id ?? $this->id;

        $this->update(['is_latest_version' => false]);

        $newVersion = $this->duplicate($user);
        $newVersion->update([
            'parent_id' => $parentId,
            'version' => $this->version + 1,
            'is_latest_version' => true,
            'name_en' => $this->name_en,
            'name_de' => $this->name_de,
        ]);

        return $newVersion;
    }
}
