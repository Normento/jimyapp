<?php

namespace App\Filament\Resources\FacebookPostResource\Pages;

use App\Filament\Resources\FacebookPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacebookPost extends EditRecord
{
    protected static string $resource = FacebookPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
