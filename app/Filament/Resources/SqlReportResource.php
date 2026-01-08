<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SqlReportResource\Pages;
use App\Filament\Resources\SqlReportResource\RelationManagers;
use App\Models\SqlReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SqlReportResource extends Resource
{
    protected static ?string $model = SqlReport::class;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('manage_sql_reports');
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Reporte')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título del Reporte')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2)
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Público (Visible para otros usuarios)')
                            ->default(false),
                    ])->columns(1),

                Forms\Components\Section::make('Consulta SQL')
                    ->description('Escribe tu consulta SELECT aquí. Cuidado: Solo lectura permitida.')
                    ->schema([
                        Forms\Components\Textarea::make('sql_query')
                            ->label('SQL Query')
                            ->required()
                            ->rows(10)
                            ->helperText('Ejemplo: SELECT id, total FROM quotes LIMIT 10')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuración de Gráfico (Opcional)')
                    ->schema([
                        Forms\Components\KeyValue::make('chart_config')
                            ->label('Configuración JSON')
                            ->keyLabel('Propiedad (type, label_col, data_col)')
                            ->valueLabel('Valor')
                            ->helperText('Claves soportadas: type (line, bar, pie), label_column (nombre columna eje X), data_column (nombre columna eje Y).'),
                    ])->collapsed(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\Action::make('run')
                    ->label('Ejecutar')
                    ->icon('heroicon-o-play')
                    ->url(fn(SqlReport $record) => \App\Filament\Pages\ReportRunner::getUrl(['report_id' => $record->id]))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListSqlReports::route('/'),
            'create' => Pages\CreateSqlReport::route('/create'),
            'edit' => Pages\EditSqlReport::route('/{record}/edit'),
        ];
    }
}
