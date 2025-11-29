<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkflowStepStatus;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasSearchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property WorkflowStepStatus $status
 */
class WorkflowStep extends Model
{
    /** @use HasFactory<\Database\Factories\WorkflowStepFactory> */
    use HasFactory;
    use HasSearchScope;
    use HasCreatorAndUpdater;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkflowStepStatus::class,
        ];
    }

    public function getSearchableColumns(): array
    {
        return [];
    }

    public function workflowTemplateStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplateStep::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
