<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('Description'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!is_string($state) || strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('users_count')
                    ->label(__('Users'))
                    ->counts('users')
                    ->sortable(),

                TextColumn::make('activities_count')
                    ->label(__('Activities'))
                    ->counts('activities')
                    ->sortable(),

                TextColumn::make('companies_count')
                    ->label(__('Companies'))
                    ->counts('companies')
                    ->sortable(),

                TextColumn::make('participants_count')
                    ->label(__('Participants'))
                    ->counts('participants')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->placeholder(__('All groups'))
                    ->trueLabel(__('Active only'))
                    ->falseLabel(__('Inactive only')),
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
