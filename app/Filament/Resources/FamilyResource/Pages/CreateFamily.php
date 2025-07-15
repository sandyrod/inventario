<?php

namespace App\Filament\Resources\FamilyResource\Pages;

use App\Filament\Resources\FamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFamily extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return FamilyResource::getUrl('index');
    }
    protected static string $resource = FamilyResource::class;
}
