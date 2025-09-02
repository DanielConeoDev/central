<?php

namespace App\Filament\Widgets;

use App\Models\EntregaItem;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class UltimasEntregas extends BaseWidget
{
    protected static ?string $heading = 'Últimas entregas';
    protected static ?int $sort = 4; // orden en el dashboard

    // 👇 Ocupa todo el ancho disponible
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder|Relation|null
    {
        return EntregaItem::query()
            ->with(['entrega', 'producto'])
            ->whereDate('created_at', today())
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('producto.nombre')
                ->label('Producto')
                ->searchable(),

            Tables\Columns\TextColumn::make('cantidad')
                ->sortable(),

            Tables\Columns\TextColumn::make('entrega.factura')
                ->label('Factura')
                ->searchable(),

            Tables\Columns\TextColumn::make('entrega.usuario.name')
                ->label('Entregado por'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha')
                ->dateTime()
                ->sortable(),
        ];
    }
}
