<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum ActivityStatus: string
{
    use HasEnumHelpers;
    case PREPARING = 'preparing';
    case STARTED = 'started';
    case FINISHED = 'finished';

    public function badgeColor(): string
    {
        return match ($this) {
            self::PREPARING => 'yellow',
            self::STARTED   => 'blue',
            self::FINISHED  => 'green',
        };
    }
}
