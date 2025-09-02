<?php

namespace App\Filament\Widgets;

use App\Models\Ingreso;
use App\Models\Descargo;
use App\Models\EntregaItem;
use App\Models\Conversion;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MovimientosChart extends ChartWidget
{
    protected static ?string $heading = 'Movimientos de Inventario';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = Carbon::now()->startOfWeek();
        $end   = Carbon::now()->endOfWeek();

        // Etiquetas de la semana en español
        $labels = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

        // Función para contar registros por día
        $contarRegistros = function($modelo) use ($start, $end) {
            return $modelo::whereBetween('created_at', [$start, $end])
                ->get()
                ->groupBy(fn($item) => Carbon::parse($item->created_at)->format('N')) // 1=lunes ... 7=domingo
                ->map(fn($grupo) => count($grupo))
                ->toArray();
        };

        $ingresos     = $contarRegistros(Ingreso::class);
        $descargos    = $contarRegistros(Descargo::class);
        $entregas     = $contarRegistros(EntregaItem::class);
        $conversiones = $contarRegistros(Conversion::class);

        // Mapear datos según las etiquetas
        $mapDatos = fn($datos) => array_map(fn($index) => (int)($datos[$index+1] ?? 0), range(0,6));

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $mapDatos($ingresos),
                    'backgroundColor' => '#34D399',
                ],
                [
                    'label' => 'Descargos',
                    'data' => $mapDatos($descargos),
                    'backgroundColor' => '#F87171',
                ],
                [
                    'label' => 'Entregas',
                    'data' => $mapDatos($entregas),
                    'backgroundColor' => '#60A5FA',
                ],
                [
                    'label' => 'Conversiones',
                    'data' => $mapDatos($conversiones),
                    'backgroundColor' => '#FBBF24',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
