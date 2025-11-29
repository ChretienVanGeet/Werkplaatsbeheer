<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ResourceStatus as ResourceStatusEnum;
use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceStatus extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<\Database\Factories\ResourceStatusFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ResourceStatusEnum::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
