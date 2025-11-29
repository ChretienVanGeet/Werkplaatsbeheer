<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasSearchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NoteAttachment> $attachments
 */
class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;
    use HasSearchScope;
    use HasCreatorAndUpdater;

    protected $guarded = [
        'id',
    ];

    public function getSearchableColumns(): array
    {
        return ['subject', 'content'];
    }

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(NoteAttachment::class);
    }
}
