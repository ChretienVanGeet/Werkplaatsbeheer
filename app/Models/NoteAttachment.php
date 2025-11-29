<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteAttachment extends Model
{
    use HasCreatorAndUpdater;
    protected $fillable = [
        'note_id',
        'file_path',
        'original_name',
        'display_name',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
