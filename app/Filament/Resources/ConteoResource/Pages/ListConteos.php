<?php

namespace App\Filament\Resources\ConteoResource\Pages;

use App\Filament\Resources\ConteoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConteos extends ListRecords
{
    protected static string $resource = ConteoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
