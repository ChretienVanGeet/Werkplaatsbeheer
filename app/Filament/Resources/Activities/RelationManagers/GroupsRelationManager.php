<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\RelationManagers;

use App\Filament\Resources\Groups\Schemas\GroupForm;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ])
            ->filters([
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
