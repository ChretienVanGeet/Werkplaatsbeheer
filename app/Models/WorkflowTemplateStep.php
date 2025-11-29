<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasSearchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTemplateStep extends Model
{
    /** @use HasFactory<\Database\Factories\WorkflowTemplateStepFactory> */
    use HasFactory;
    use HasSearchScope;
    use HasCreatorAndUpdater;

    protected $guarded = [
        'id',
    ];

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function getSearchableColumns(): array
    {
        return ['name'];
    }
}
