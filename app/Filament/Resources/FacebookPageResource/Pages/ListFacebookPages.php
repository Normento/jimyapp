<?php

namespace App\Filament\Resources\FacebookPageResource\Pages;

use App\Filament\Resources\FacebookPageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListFacebookPages extends ListRecords
{
    protected static string $resource = FacebookPageResource::class;

    protected function getActions(): array
    {
        return [
            // Action pour créer une nouvelle FacebookPage manuellement
            //Actions\CreateAction::make(),

            // Action personnalisée pour connecter une Page Facebook via OAuth
            Actions\Action::make('connectFacebookPage')
                ->label('Connecter une Page Facebook')
                ->url(route('auth.facebook'))
                ->icon('heroicon-o-plus')
                ->color('primary')
        ];
    }
}
