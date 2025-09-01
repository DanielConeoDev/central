<?php

namespace App\Filament\Resources\IngresoResource\Pages;

use App\Filament\Resources\IngresoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngreso extends EditRecord
{
    protected static string $resource = IngresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
