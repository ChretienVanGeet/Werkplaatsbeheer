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

/**
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $city
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 *
 * @method static \Database\Factories\ParticipantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Participant extends Model implements HasNotesContract, HasWorkflowsContract
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\ParticipantFactory> */
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

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_participant');
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'phone', 'email', 'city', 'comments'];
    }
}
