<?php

namespace App\Filament\Widgets;

use App\Models\Entrega;
use App\Models\Ingreso;
use App\Models\Descargo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;

class InventarioOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    // Columnas en el dashboard
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    // Intervalo de refresco automático (opcional)
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [
            Stat::make('Entregas hoy', Entrega::whereDate('created_at', today())->count())
                ->description('Número de entregas realizadas hoy')
                ->descriptionIcon('heroicon-m-truck', IconPosition::Before)
                ->color('primary')
                ->chart([3, 5, 2, 4, 6, 7, 3]), // mini-chart opcional

            Stat::make('Ingresos hoy', Ingreso::whereDate('created_at', today())->count())
                ->description('Unidades ingresadas hoy')
                ->descriptionIcon('heroicon-m-inbox-stack', IconPosition::Before)
                ->color('success')
                ->chart([5, 7, 6, 8, 4, 3, 5]),

            Stat::make('Descargos hoy', Descargo::whereDate('created_at', today())->count())
                ->description('Unidades descargadas hoy')
                ->descriptionIcon('heroicon-m-arrow-down-left', IconPosition::Before)
                ->color('danger')
                ->chart([2, 3, 1, 4, 3, 2, 1]),
        ];
    }
}
