<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\UserGroupScope;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasSearchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\WorkflowTemplateFactory> */
    use HasFactory;
    use HasSearchScope;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserGroupScope());
    }

    public function workflowTemplateSteps(): HasMany
    {
        return $this->hasMany(WorkflowTemplateStep::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_workflow_template');
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'id'];
    }
}
