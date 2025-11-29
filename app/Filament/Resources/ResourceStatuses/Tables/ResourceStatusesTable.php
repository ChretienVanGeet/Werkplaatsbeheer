<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResourceStatuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResourceStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('resource.name')
                    ->label(__('Resource'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('activity.name')
                    ->label(__('Activity'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
