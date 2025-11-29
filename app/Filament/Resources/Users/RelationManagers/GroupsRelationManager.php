<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Groups\Schemas\GroupForm;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->placeholder(__('All groups'))
                    ->trueLabel(__('Active only'))
                    ->falseLabel(__('Inactive only')),
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
