<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum ResourceStatus: string
{
    use HasEnumHelpers;

    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case OCCUPIED = 'occupied';
    case MAINTENANCE = 'maintenance';

    public function badgeColor(): string
    {
        return match ($this) {
            self::AVAILABLE => 'gray',
            self::RESERVED => 'yellow',
            self::OCCUPIED => 'green',
            self::MAINTENANCE => 'red',
        };
    }
}
