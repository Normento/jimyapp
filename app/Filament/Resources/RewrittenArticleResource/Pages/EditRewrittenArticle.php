<?php

namespace App\Filament\Resources\RewrittenArticleResource\Pages;

use App\Filament\Resources\RewrittenArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRewrittenArticle extends EditRecord
{
    protected static string $resource = RewrittenArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
