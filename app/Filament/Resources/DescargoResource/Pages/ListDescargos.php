<?php

namespace App\Filament\Resources\DescargoResource\Pages;

use App\Filament\Resources\DescargoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDescargos extends ListRecords
{
    protected static string $resource = DescargoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
