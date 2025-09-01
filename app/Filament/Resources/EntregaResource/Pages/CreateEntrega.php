<?php

namespace App\Filament\Resources\EntregaResource\Pages;

use App\Filament\Resources\EntregaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEntrega extends CreateRecord
{
    protected static string $resource = EntregaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
