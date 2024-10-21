<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\FacebookPage;
use Filament\Resources\Resource;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FacebookPageResource\Pages;
use App\Filament\Resources\FacebookPageResource\RelationManagers;

class FacebookPageResource extends Resource
{
    protected static ?string $model = FacebookPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Pages Facebook';
    protected static ?string $pluralModelLabel = 'Pages Facebook';
    protected static ?string $modelLabel = 'Page Facebook';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('facebook_page_id')
                    ->disabled()
                    ->required(),
                Forms\Components\Textarea::make('perms')
                    ->label('Permissions')
                    ->disabled()
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('access_token')
                    ->label('Access Token')
                    ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->label('Nom du page'),
                Tables\Columns\TextColumn::make('facebook_page_id')->sortable()->searchable()->label('ID'),
                Tables\Columns\TextColumn::make('perms')->limit(50),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListFacebookPages::route('/'),
            'create' => Pages\CreateFacebookPage::route('/create'),
            'edit' => Pages\EditFacebookPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /* return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]); */

        return parent::getEloquentQuery()->where('user_id', Auth::id());

    }


    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'ID' => $record->id,
            'Nom' => $record->name,
            'Page Facebook ID' => $record->facebook_page_id,
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('Connecter une Page Facebook')
                ->label('Connecter une Page Facebook')
                ->url(route('auth.facebook'))
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
