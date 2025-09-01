<?php

namespace App\Filament\Widgets;

use App\Models\Ingreso;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class UltimosIngresos extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Últimos ingresos';
    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder|Relation|null
    {
        return Ingreso::query()
            ->when($this->filters['startDate'] ?? null, fn (Builder $q, $start) => $q->whereDate('created_at', '>=', $start))
            ->when($this->filters['endDate'] ?? null, fn (Builder $q, $end) => $q->whereDate('created_at', '<=', $end))
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('producto_codigo')
                ->label('Producto')
                ->searchable(),

            Tables\Columns\TextColumn::make('cantidad')
                ->sortable(),

            Tables\Columns\TextColumn::make('factura')
                ->label('Factura')
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha')
                ->dateTime()
                ->sortable(),
        ];
    }
}
