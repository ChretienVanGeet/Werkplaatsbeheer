<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasNotesContract;
use App\Contracts\HasWorkflowsContract;
use App\Enums\ActivityStatus;
use App\Scopes\UserGroupScope;
use App\Traits\FiltersByUserGroups;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasNotes;
use App\Traits\HasSearchScope;
use App\Traits\HasWorkflows;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property ActivityStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $companies
 * @property-read int|null $companies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Participant> $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 *
 * @method static \Database\Factories\ActivityFactory factory($count = null, $state = [])
 * @method static Builder<static>|Activity newModelQuery()
 * @method static Builder<static>|Activity newQuery()
 * @method static Builder<static>|Activity query()
 * @method static Builder<static>|Activity whereCreatedAt($value)
 * @method static Builder<static>|Activity whereEndDate($value)
 * @method static Builder<static>|Activity whereId($value)
 * @method static Builder<static>|Activity whereName($value)
 * @method static Builder<static>|Activity whereStartDate($value)
 * @method static Builder<static>|Activity whereStatus($value)
 * @method static Builder<static>|Activity whereUpdatedAt($value)
 * @method static Builder<static>|Activity search(string $term)
 * @method static MorphMany notes()
 *
 * @mixin \Eloquent
 */
class Activity extends Model implements HasNotesContract, HasWorkflowsContract
{
    use FiltersByUserGroups;
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\ActivityFactory> */
    use HasFactory;

    use HasNotes;
    use HasSearchScope;
    use HasWorkflows;

    protected $guarded = [
        'id',
    ];

    protected $attributes = [
        'status' => ActivityStatus::PREPARING->value,
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ActivityStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new UserGroupScope);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class)
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_activity');
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'activity_resource');
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'start_date', 'end_date'];
    }
}
