<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use App\Models\Ingreso;
use App\Models\Descargo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class InventarioOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getStats(): array
    {
        $start = $this->filters['startDate'] ?? null;
        $end   = $this->filters['endDate'] ?? null;

        return [
            Stat::make('Productos activos',
                Producto::where('estado', 'activo')->count()
            ),

            Stat::make('Total ingresos',
                Ingreso::when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
                       ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
                       ->sum('cantidad')
            )->description('Unidades ingresadas'),

            Stat::make('Total descargos',
                Descargo::when($start, fn($q) => $q->whereDate('created_at', '>=', $start))
                        ->when($end, fn($q) => $q->whereDate('created_at', '<=', $end))
                        ->sum('cantidad')
            )->description('Unidades descargadas'),
        ];
    }
}
