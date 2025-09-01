<?php

namespace App\Filament\Widgets;

use App\Models\Ingreso;
use App\Models\Descargo;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class MovimientosChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Movimientos de Inventario';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = $this->filters['startDate'] ?? Carbon::now()->startOfYear();
        $end   = $this->filters['endDate'] ?? Carbon::now();

        $ingresos = Ingreso::selectRaw('MONTH(created_at) as mes, SUM(cantidad) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $descargos = Descargo::selectRaw('MONTH(created_at) as mes, SUM(cantidad) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $labels = range(1, 12);

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => array_map(fn($m) => $ingresos[$m] ?? 0, $labels),
                ],
                [
                    'label' => 'Descargos',
                    'data' => array_map(fn($m) => $descargos[$m] ?? 0, $labels),
                ],
            ],
            'labels' => array_map(fn($m) => Carbon::create()->month($m)->shortMonthName, $labels),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
