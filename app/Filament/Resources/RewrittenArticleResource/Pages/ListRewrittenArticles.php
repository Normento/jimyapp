<?php

namespace App\Filament\Resources\RewrittenArticleResource\Pages;

use App\Filament\Resources\RewrittenArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRewrittenArticles extends ListRecords
{
    protected static string $resource = RewrittenArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
