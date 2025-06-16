<?php

namespace App\Filament\Resources\InputResource\Pages;

use App\Filament\Resources\InputResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInputs extends ListRecords
{
    protected static string $resource = InputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
