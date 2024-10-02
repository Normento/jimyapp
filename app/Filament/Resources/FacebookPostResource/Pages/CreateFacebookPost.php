<?php

namespace App\Filament\Resources\FacebookPostResource\Pages;

use App\Filament\Resources\FacebookPostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFacebookPost extends CreateRecord
{
    protected static string $resource = FacebookPostResource::class;
}
