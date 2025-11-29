<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasSearchScope;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $companies
 * @property-read int|null $companies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Participant> $participants
 * @property-read int|null $participants_count
 *
 * @method static \Database\Factories\GroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group search(string $term)
 *
 * @mixin \Eloquent
 */
class Group extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;
    use HasSearchScope;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::created(function (Group $group): void {
            $adminIds = User::query()
                ->where('role', UserRole::Administrator)
                ->pluck('id');

            if ($adminIds->isNotEmpty()) {
                $group->users()->syncWithoutDetaching($adminIds);
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'group_activity');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'group_company');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'group_participant');
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'description'];
    }
}
