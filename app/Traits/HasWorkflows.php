<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read Collection<int, Workflow> $workflows
 */
trait HasWorkflows
{
    public function workflows(): MorphMany
    {
        return $this->morphMany(Workflow::class, 'subject');
    }
}
