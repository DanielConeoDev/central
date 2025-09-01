<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    public function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 4, // 4 columnas en pantallas grandes
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    DatePicker::make('startDate')->label('Desde'),
                    DatePicker::make('endDate')->label('Hasta'),
                ]),
        ];
    }
}
