<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasEnumHelpers;

enum UserRole: string
{
    use HasEnumHelpers;

    case Reader = 'reader';
    case Editor = 'editor';
    case Administrator = 'administrator';
}
