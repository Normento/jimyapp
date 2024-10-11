<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationConfigResource\Pages;
use App\Filament\Resources\PublicationConfigResource\RelationManagers;
use App\Models\PublicationConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PublicationConfigResource extends Resource
{
    protected static ?string $model = PublicationConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Configurations de Publication';
    protected static ?string $pluralModelLabel = 'Configurations de Publication';
    protected static ?string $modelLabel = 'Configuration de Publication';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('page_id')
                    ->label('Page')
                    ->relationship('page', 'name')
                    ->required(),
                Forms\Components\TextInput::make('number_of_posts_per_day')
                    ->label('Nombre de Publications par Jour')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100),
                Forms\Components\TextInput::make('interval_minutes')
                    ->label('Intervalle entre les Publications (minutes)')
                    ->required()
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(1440),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Date de Début')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Date de Fin')
                    ->required()
                    ->afterOrEqual('start_date'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activer la Configuration')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('league.name')->label('Ligue')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('number_of_posts_per_day')->label('Posts/Jour')->sortable(),
                Tables\Columns\TextColumn::make('interval_minutes')->label('Intervalle (min)')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Status'),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable()->label('Date de début'),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable()->label('Date de fin'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->trueLabel('Actif')
                    ->falseLabel('Inactif')
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPublicationConfigs::route('/'),
            'create' => Pages\CreatePublicationConfig::route('/create'),
            'edit' => Pages\EditPublicationConfig::route('/{record}/edit'),
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
