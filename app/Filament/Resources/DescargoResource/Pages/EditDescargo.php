<?php

namespace App\Filament\Resources\DescargoResource\Pages;

use App\Filament\Resources\DescargoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDescargo extends EditRecord
{
    protected static string $resource = DescargoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
}
