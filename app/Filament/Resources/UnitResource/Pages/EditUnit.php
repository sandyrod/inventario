<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnit extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return UnitResource::getUrl('index');
    }
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
