<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string|null $name
 * @property string|null $role
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $location
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 * @method static \Database\Factories\CompanyContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompanyContact whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class CompanyContact extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyContactFactory> */
    use HasFactory;
    use HasCreatorAndUpdater;

    protected $guarded = [
        'id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

}
