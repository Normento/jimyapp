<?php

namespace App\Filament\Resources\FacebookPageResource\Pages;

use App\Filament\Resources\FacebookPageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacebookPage extends EditRecord
{
    protected static string $resource = FacebookPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
