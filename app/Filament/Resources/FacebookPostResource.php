<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacebookPostResource\Pages;
use App\Filament\Resources\FacebookPostResource\RelationManagers;
use App\Models\FacebookPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacebookPostResource extends Resource
{
    protected static ?string $model = FacebookPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationLabel = 'Publications Facebook';
    protected static ?string $pluralModelLabel = 'Publications Facebook';
    protected static ?string $modelLabel = 'Publication Facebook';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rewritten_article_id')
                    ->label('Article Réécrit')
                    ->relationship('rewrittenArticle', 'title')
                    ->required(),
                Forms\Components\Select::make('facebook_page_id')
                    ->label('Page Facebook')
                    ->relationship('facebookPage', 'name')
                    ->required(),
                Forms\Components\TextInput::make('facebook_post_id')
                    ->label('ID de la Publication Facebook')
                    ->required()
                    ->maxLength(255)
                    ->unique(FacebookPost::class, 'facebook_post_id', ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->options([
                        'posted' => 'Publié',
                        'failed' => 'Échoué',
                    ])
                    ->default('posted')
                    ->required(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Date de Publication Programmée')
                    ->required(),
                Forms\Components\DateTimePicker::make('posted_at')
                    ->label('Date de Publication Réelle')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\ImageColumn::make('rewrittenArticle.image_url')->label('Image')->sortable()->wrap()->toggleable()->size(150)->searchable(),
                Tables\Columns\TextColumn::make('rewrittenArticle.title')->label('Article')->sortable()->wrap()->toggleable()->searchable(),
                Tables\Columns\TextColumn::make('facebookPage.name')->label('Page Facebook')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable()->badge()->colors([
                    'success' => 'posted',
                    'danger' => 'failed',
                ]),
                Tables\Columns\TextColumn::make('posted_at')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->deferLoading()
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'posted' => 'Publié',
                        'failed' => 'Échoué',
                    ]),
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from'),
                        Forms\Components\DatePicker::make('scheduled_until'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['scheduled_from'], fn($q) => $q->whereDate('scheduled_at', '>=', $data['scheduled_from']))
                            ->when($data['scheduled_until'], fn($q) => $q->whereDate('scheduled_at', '<=', $data['scheduled_until']));
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
            'index' => Pages\ListFacebookPosts::route('/'),
            'create' => Pages\CreateFacebookPost::route('/create'),
            'edit' => Pages\EditFacebookPost::route('/{record}/edit'),
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
