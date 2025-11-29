<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

trait HasEnumHelpers
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->name => $case->getLabel()])
            ->toArray();
    }

    public function getLabel(): string
    {
        return self::trans($this);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => ucfirst($case->name)])
            ->toArray();
    }

    public static function casesArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->name => $case->value])
            ->toArray();
    }

    public static function list(): array
    {
        return collect(self::cases())->toArray();
    }

    public static function trans(self $case, bool $translate = true): string
    {
        return __(self::getTranslateKey($translate) . '.' . strtolower($case->value));
    }

    private static function getTranslateKey(bool|string $key): string
    {
        if (is_string($key)) {
            return $key;
        }
        $namespace = explode('\\', get_called_class());

        return ('enums.' . Str::snake($namespace[count($namespace) - 1]));
    }
}
