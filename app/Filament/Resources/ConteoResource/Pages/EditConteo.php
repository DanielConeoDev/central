<?php

namespace App\Filament\Resources\ConteoResource\Pages;

use App\Filament\Resources\ConteoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConteo extends EditRecord
{
    protected static string $resource = ConteoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
