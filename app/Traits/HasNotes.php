<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Note;

trait HasNotes
{
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}
