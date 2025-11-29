<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('mobile'),
                TextInput::make('organisation'),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($record) => $record === null)
                    ->label('Password'),
                Select::make('role')
                    ->label('Rol')
                    ->options(
                        collect(UserRole::cases())
                            ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->getLabel()])
                    )
                    ->default(UserRole::Reader->value)
                    ->required()
                    ->native(false),
                TextInput::make('created_by')
                    ->default(auth()->id())
                    ->numeric(),
                TextInput::make('updated_by')
                    ->default(auth()->id())
                    ->numeric(),
            ]);
    }
}
