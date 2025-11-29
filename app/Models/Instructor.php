<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasNotesContract;
use App\Contracts\HasWorkflowsContract;
use App\Scopes\UserGroupScope;
use App\Traits\FiltersByUserGroups;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasNotes;
use App\Traits\HasSearchScope;
use App\Traits\HasWorkflows;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
class Instructor extends Model implements HasNotesContract, HasWorkflowsContract
{
    use FiltersByUserGroups;
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\InstructorFactory> */
    use HasFactory;

    use HasNotes;
    use HasSearchScope;
    use HasWorkflows;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserGroupScope);
    }

    protected function casts(): array
    {
        return [];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_instructor');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(InstructorAssignment::class);
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'instructor_assignments')
            ->withPivot(['starts_at', 'ends_at', 'activity_id'])
            ->withTimestamps();
    }

    public function supportedResources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'instructor_resource');
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'description'];
    }
}
