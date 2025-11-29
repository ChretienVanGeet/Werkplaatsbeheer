<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkflowStepStatus;
use App\Scopes\WorkflowGroupScope;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasSearchScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Collection<int, WorkflowStep> $workflowSteps
 * @property-read WorkflowTemplate|null $workflowTemplate
 */
class Workflow extends Model
{
    /** @use HasFactory<\Database\Factories\WorkflowFactory> */
    use HasFactory;
    use HasSearchScope;
    use HasCreatorAndUpdater;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WorkflowGroupScope());
    }

    public function getSearchableColumns(): array
    {
        return [
            'workflowTemplate.name',
            'subject.name',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function workflowSteps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }

    public function getSubjectLinkAttribute(): ?string
    {
        return match ($this->subject_type) {
            'App\Models\Company'     => route('companies.show', $this->subject_id),
            'App\Models\Participant' => route('participants.show', $this->subject_id),
            'App\Models\Activity'    => route('activities.show', $this->subject_id),
            default                  => null,
        };
    }

    public function getSubjectIconNameAttribute(): ?string
    {
        return match ($this->subject_type) {
            'App\Models\Company'     => 'building-office-2',
            'App\Models\Participant' => 'user',
            'App\Models\Activity'    => 'calendar',
            default                  => null,
        };
    }

    public function getProgressPercentageAttribute(): int
    {
        // When forgotten to eager load the counts, this will still make the percentage work
        if (!isset($this->workflow_steps_count, $this->completed_steps_count)) {
            $this->loadCount([
                'workflowSteps',
                'workflowSteps as completed_steps_count' => fn ($q) => $q->where('status', WorkflowStepStatus::FINISHED->value),
            ]);
        }

        $total = $this->workflow_steps_count ?? 0;
        $completed = $this->completed_steps_count ?? 0;

        if ($total === 0) {
            return 0;
        }

        return (int) round(($completed / $total) * 100);
    }
}
