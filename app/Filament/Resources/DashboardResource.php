<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardResource\Pages;
use App\Models\Dashboard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DashboardResource extends Resource
{
    protected static ?string $model = Dashboard::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $title = 'Diseñador de Dashboards';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración del Dashboard')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nombre del Dashboard')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug / URL')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Dashboard::class, 'slug', ignoreRecord: true),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icono (Heroicon)')
                            ->default('heroicon-o-presentation-chart-bar')
                            ->placeholder('heroicon-o-user')
                            ->helperText('Busca iconos en heroicons.com'),
                        Forms\Components\Select::make('color')
                            ->label('Color del Tema')
                            ->options([
                                'primary' => 'Azul',
                                'success' => 'Verde',
                                'warning' => 'Naranja',
                                'danger' => 'Rojo',
                            ])
                            ->default('primary'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Ruta/URL'),
                Tables\Columns\IconColumn::make('icon')
                    ->label('Icono'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboards::route('/'),
            'create' => Pages\CreateDashboard::route('/create'),
            'edit' => Pages\EditDashboard::route('/{record}/edit'),
        ];
    }
}
