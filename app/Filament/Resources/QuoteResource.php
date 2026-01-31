<?php

namespace App\Filament\Resources;

use App\Enums\QuoteStatus;
use App\Enums\SalesTerm;
use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->searchable(),

                TextColumn::make('Status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('workOrder.id')
                    ->label('WO')
                    ->placeholder('Sin WO')
                    ->badge()
                    ->color(fn($state) => $state ? 'danger' : 'gray')
                    ->formatStateUsing(fn($state) => $state ? "WO #{$state}" : 'Sin WO'),

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

                TernaryFilter::make('work_order_status')
                    ->label('Filtro WO')
                    ->placeholder('Todas')
                    ->trueLabel('Con WO')
                    ->falseLabel('Sin WO')
                    ->queries(
                        true: fn(Builder $query) => $query->has('workOrder'),
                        false: fn(Builder $query) => $query->doesntHave('workOrder'),
                    ),

                SelectFilter::make('SalesTerm')
                    ->label('Tipo de Pago')
                    ->multiple()
                    ->options([
                        'COD' => 'C.O.D.',
                        'CREDITO' => 'Crédito',
                        'CREDIT-COD' => 'Crédito COD',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        $terms = [];
                        foreach ($data['values'] as $value) {
                            if ($value === 'COD') {
                                $terms = array_merge($terms, ['COD', 'C.O.D.']);
                            } elseif ($value === 'CREDITO') {
                                $terms = array_merge($terms, ['CREDITO', 'CREDIT']);
                            } elseif ($value === 'CREDIT-COD') {
                                $terms = array_merge($terms, ['CREDIT-COD', 'CREDIT COD']);
                            } else {
                                $terms[] = $value;
                            }
                        }
                        return $query->whereIn('SalesTerm', $terms);
                    }),

                SelectFilter::make('Status')
                    ->label('Estado')
                    ->multiple()
                    ->options([
                        'APPROVED' => 'Aprobada',
                        'BILLED' => 'Facturada',
                        'ACTIVE' => 'Activa',
                        'PENDING' => 'Pendiente',
                        'CANCELLED' => 'Cancelada',
                        'ABORTED' => 'Abortada',
                        'VENTA-PERDIDA' => 'Venta Perdida',
                        'EXPIRED' => 'Expirada',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        $statuses = [];
                        foreach ($data['values'] as $value) {
                            $statuses[] = $value;
                            if ($value === 'APPROVED') {
                                $statuses[] = 'APROVED';
                            }
                        }
                        return $query->whereIn('Status', $statuses);
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
