<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\HasNotesContract;
use App\Contracts\HasWorkflowsContract;
use App\Scopes\UserGroupScope;
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
 * @property string|null $industry
 * @property string|null $comments
 * @property string|null $locations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 *
 * @method static \Database\Factories\CompanyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereIndustry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLocations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Company extends Model implements HasNotesContract, HasWorkflowsContract
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;
    use HasNotes;
    use HasSearchScope;
    use HasWorkflows;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserGroupScope());
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }

    public function companyContacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_company');
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'comments', 'industry'];
    }
}
