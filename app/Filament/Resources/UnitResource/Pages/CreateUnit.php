<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnit extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return UnitResource::getUrl('index');
    }
    protected static string $resource = UnitResource::class;
}
