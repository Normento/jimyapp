<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewrittenArticleResource\Pages;
use App\Filament\Resources\RewrittenArticleResource\RelationManagers;
use App\Models\RewrittenArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewrittenArticleResource extends Resource
{
    protected static ?string $model = RewrittenArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Articles Réécrits';
    protected static ?string $pluralModelLabel = 'Articles Réécrits';
    protected static ?string $modelLabel = 'Article Réécrit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('page_id')
                    ->label('Page')
                    ->relationship('page', 'name')
                    ->required(),
                Forms\Components\Select::make('league_id')
                    ->label('Ligue')
                    ->relationship('league', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(1000),
                Forms\Components\RichEditor::make('content')
                    ->required(),
                Forms\Components\TextInput::make('url')
                    ->url()
                    ->nullable(),
                Forms\Components\TextInput::make('image_url')
                    ->url()
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'processed' => 'Traitée',
                        'failed' => 'Échouée',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('league.name')->label('Ligue')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge()->colors([
                    'secondary' => 'pending',
                    'success' => 'processed',
                    'danger' => 'failed',
                ]),
                Tables\Columns\TextColumn::make('published_at')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'processed' => 'Traitée',
                        'failed' => 'Échouée',
                    ]),
                    Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['created_from'], fn($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRewrittenArticles::route('/'),
            'create' => Pages\CreateRewrittenArticle::route('/create'),
            'edit' => Pages\EditRewrittenArticle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
