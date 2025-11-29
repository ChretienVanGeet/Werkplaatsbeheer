<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum WorkflowStepStatus: string
{
    use HasEnumHelpers;
    case CREATED = 'created';
    case IN_PROGRESS = 'in_progress';
    case ON_HOLD = 'on_hold';
    case FINISHED = 'finished';

    public function badgeColor(): string
    {
        return match ($this) {
            self::CREATED     => 'gray',
            self::IN_PROGRESS => 'blue',
            self::ON_HOLD     => 'yellow',
            self::FINISHED    => 'green'
        };
    }
}
