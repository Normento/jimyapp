<?php

namespace App\Filament\Resources\PublicationConfigResource\Pages;

use App\Filament\Resources\PublicationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPublicationConfigs extends ListRecords
{
    protected static string $resource = PublicationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
