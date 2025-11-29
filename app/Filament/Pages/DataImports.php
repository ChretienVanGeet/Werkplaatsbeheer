<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Imports\CompanyImporter;
use App\Filament\Imports\ParticipantImporter;
use BackedEnum;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class DataImports extends Page implements HasActions
{
    use \Filament\Actions\Concerns\InteractsWithActions;
    public static function getNavigationLabel(): string
    {
        return __('Imports');
    }

    public function getHeading(): string
    {
        return __('Imports');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownTray;
    protected static string | UnitEnum | null $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 90;
    protected string $view = 'filament.pages.data-imports';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function participantsAction(): ImportAction
    {
        return ImportAction::make('participants')
            ->label(__('Import Participants'))
            ->importer(ParticipantImporter::class);
    }

    public function companiesAction(): ImportAction
    {
        return ImportAction::make('companies')
            ->label(__('Import Companies'))
            ->importer(CompanyImporter::class);
    }
}
