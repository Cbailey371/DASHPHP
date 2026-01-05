<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn; // Filament v3 usa TextColumn->badge()
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportAction; // Requiere pxlrbt/filament-excel o similar si es nativo v3

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Cotizaciones';
    protected static ?string $navigationGroup = 'Operaciones';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_quotes');
    }

    // Desactivar creación/edición desde Filament pues es solo lectura
    public static function canCreate(): bool
    {
        return false;
    }
    // public static function canEdit(Model $record): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Formulario de solo lectura si se desea ver detalles
                Forms\Components\TextInput::make('id')
                    ->label('Nro Cotización')
                    ->disabled(),
                Forms\Components\TextInput::make('cliente')
                    ->label('Cliente')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('Date', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.Empresa')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('Total')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('Date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('SalesTerm')
                    ->label('Tipo de Pago')
                    ->badge()
                    ->color(fn(string $state): string => match (strtoupper($state)) {
                        'COD' => 'success',
                        'C.O.D.' => 'success',
                        'CREDITO' => 'warning',
                        'CREDIT' => 'warning',
                        'CREDIT-COD' => 'info',
                        'CREDIT COD' => 'info',
                        default => 'gray',
                    })
                    ->searchable(), // Permitir buscar también por término de pago

                TextColumn::make('Status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match (strtoupper($state)) {
                        'APROVED' => 'success', // Typo en BD
                        'APPROVED' => 'success',
                        'BILLED' => 'success',
                        'ACTIVE' => 'primary',
                        'PENDING' => 'warning',
                        'CANCELLED' => 'danger',
                        'ABORTED' => 'danger',
                        'VENTA-PERDIDA' => 'danger',
                        'EXPIRED' => 'gray',
                        default => 'info',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('workOrder.id')
                    ->label('Orden de Trabajo')
                    ->placeholder('PENDIENTE')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state ? "✅ WO #{$state}" : 'PENDIENTE'),

                TextColumn::make('days_old')
                    ->label('Días Antigüedad')
                    ->state(function (Quote $record) {
                        return (int) $record->days_old;
                    })
                    ->color(fn(Quote $record): string => match (true) {
                        $record->days_old > 10 => 'danger',
                        $record->days_old >= 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('Date', $direction === 'asc' ? 'desc' : 'asc');
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('Date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('Date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('Date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators[] = 'Desde ' . \Carbon\Carbon::parse($data['date_from'])->toFormattedDateString();
                        }
                        if ($data['date_until'] ?? null) {
                            $indicators[] = 'Hasta ' . \Carbon\Carbon::parse($data['date_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),

                TernaryFilter::make('no_work_order')
                    ->label('Sin Orden de Trabajo')
                    ->placeholder('Todas')
                    ->trueLabel('Sin WO')
                    ->falseLabel('Con WO')
                    ->queries(
                        true: fn(Builder $query) => $query->doesntHave('workOrder'),
                        false: fn(Builder $query) => $query->has('workOrder'),
                    ),

                SelectFilter::make('SalesTerm')
                    ->label('Tipo de Pago')
                    ->multiple()
                    ->options([
                        'C.O.D.' => 'C.O.D.',
                        'CREDIT' => 'Crédito',
                        'CREDIT-COD' => 'Crédito COD',
                    ]),

                SelectFilter::make('Status')
                    ->label('Estado')
                    ->multiple()
                    ->options([
                        'APROVED' => 'Aprobada', // Typo en BD
                        'APPROVED' => 'Aprobada (Corregido)',
                        'BILLED' => 'Facturada',
                        'ACTIVE' => 'Activa',
                        'PENDING' => 'Pendiente',
                        'CANCELLED' => 'Cancelada',
                        'ABORTED' => 'Abortada',
                        'VENTA-PERDIDA' => 'Venta Perdida',
                        'EXPIRED' => 'Expirada',
                    ]),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(), // Deshabilitado
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make(),
                ]),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make('table')
                            ->fromTable()
                            ->withFilename(fn($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                            ->withColumns([
                                \pxlrbt\FilamentExcel\Columns\Column::make('days_old')
                                    ->heading('Días Antigüedad')
                                    ->formatStateUsing(fn($state) => (int) $state),
                                \pxlrbt\FilamentExcel\Columns\Column::make('workOrder.id')->heading('Orden de Trabajo'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
        ];
    }
}
