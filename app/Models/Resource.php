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
 * @property string $machine_type
 * @property int $instructor_capacity Instructor load percentage required per assignment (1-100)
 * @property string|null $description
 */
class Resource extends Model implements HasNotesContract, HasWorkflowsContract
{
    use FiltersByUserGroups;
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\ResourceFactory> */
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
        return [
            'instructor_capacity' => 'integer',
        ];
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_resource');
    }

    public function supportedInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'instructor_resource');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_resource');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(ResourceStatus::class);
    }

    public function instructorAssignments(): HasMany
    {
        return $this->hasMany(InstructorAssignment::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'instructor_assignments')
            ->withPivot(['starts_at', 'ends_at', 'activity_id'])
            ->withTimestamps();
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'machine_type', 'description'];
    }
}
