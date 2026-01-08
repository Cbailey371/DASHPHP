<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomWidgetResource\Pages;
use App\Filament\Resources\CustomWidgetResource\RelationManagers;
use App\Models\CustomWidget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomWidgetResource extends Resource
{
    protected static ?string $model = CustomWidget::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('manage_custom_widgets');
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración General')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título del Widget')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Gráfico')
                            ->options([
                                'stat' => 'Estadística (Número)',
                                'chart_line' => 'Gráfico de Línea',
                                'chart_bar' => 'Gráfico de Barras',
                                'chart_pie' => 'Gráfico Circular',
                            ])
                            ->required()
                            ->default('stat')
                            ->reactive(),
                        Forms\Components\Select::make('dashboard_id')
                            ->label('Ubicación (Dashboard)')
                            ->relationship('dashboard', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('color')
                            ->label('Color')
                            ->options([
                                'primary' => 'Azul (Primary)',
                                'success' => 'Verde (Success)',
                                'warning' => 'Amarillo (Warning)',
                                'danger' => 'Rojo (Danger)',
                                'info' => 'Celeste (Info)',
                            ])
                            ->default('primary')
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Fuente de Datos')
                    ->schema([
                        Forms\Components\Radio::make('dataSource')
                            ->label('Origen de Datos')
                            ->options([
                                'model' => 'Modelo (Agregación directa)',
                                'sql' => 'Reporte SQL Guardado',
                            ])
                            ->default('model')
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($set, $record) {
                                if ($record?->sql_report_id) {
                                    $set('dataSource', 'sql');
                                }
                            }),

                        // Campos para Modelo
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('model')
                                ->label('Modelo / Tabla')
                                ->options([
                                    'Quote' => 'Cotizaciones (Quotes)',
                                    'WorkOrder' => 'Ordenes de Trabajo (WorkOrders)',
                                ])
                                ->required(fn(Forms\Get $get) => $get('dataSource') === 'model'),
                            Forms\Components\Select::make('aggregate_function')
                                ->label('Operación')
                                ->options([
                                    'count' => 'Contar Registros (Cantidad total)',
                                    'sum' => 'Sumar Columna (Total dinero/bultos)',
                                    'avg' => 'Promedio',
                                    'min' => 'Mínimo',
                                    'max' => 'Máximo',
                                ])
                                ->default('count')
                                ->reactive(),
                            Forms\Components\TextInput::make('aggregate_column')
                                ->label('Columna a Sumar/Promediar')
                                ->placeholder('Ej: total_amount')
                                ->helperText('Solo necesario si eliges Sumar o Promedio.')
                                ->visible(fn(Forms\Get $get) => in_array($get('aggregate_function'), ['sum', 'avg', 'min', 'max'])),
                            Forms\Components\TextInput::make('date_column')
                                ->label('Columna de Fecha')
                                ->default('created_at')
                                ->helperText('Campo usado para agrupar por mes/año en gráficos.'),
                        ])
                            ->visible(fn(Forms\Get $get) => $get('dataSource') === 'model')
                            ->columns(2),

                        // Campos para SQL
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('sql_report_id')
                                ->label('Seleccionar Reporte SQL')
                                ->relationship('sqlReport', 'title')
                                ->required(fn(Forms\Get $get) => $get('dataSource') === 'sql')
                                ->searchable()
                                ->preload()
                                ->helperText('La consulta SQL debe devolver al menos dos columnas (Label y Valor).'),
                        ])
                            ->visible(fn(Forms\Get $get) => $get('dataSource') === 'sql')
                            ->columns(1),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'stat',
                        'info' => 'chart_line',
                        'success' => 'chart_bar',
                        'warning' => 'chart_pie',
                    ]),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aggregate_function')
                    ->label('Operación')
                    ->badge(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCustomWidgets::route('/'),
            'create' => Pages\CreateCustomWidget::route('/create'),
            'edit' => Pages\EditCustomWidget::route('/{record}/edit'),
        ];
    }
}
