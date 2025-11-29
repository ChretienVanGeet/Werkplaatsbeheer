<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @method mixed getKey()
 **/
interface HasNotesContract
{
    public function notes(): MorphMany;
}
