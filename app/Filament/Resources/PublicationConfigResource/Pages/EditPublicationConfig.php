<?php

namespace App\Filament\Resources\PublicationConfigResource\Pages;

use App\Filament\Resources\PublicationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPublicationConfig extends EditRecord
{
    protected static string $resource = PublicationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
