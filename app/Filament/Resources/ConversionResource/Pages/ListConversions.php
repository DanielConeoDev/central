<?php

namespace App\Filament\Resources\ConversionResource\Pages;

use App\Filament\Resources\ConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConversions extends ListRecords
{
    protected static string $resource = ConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
