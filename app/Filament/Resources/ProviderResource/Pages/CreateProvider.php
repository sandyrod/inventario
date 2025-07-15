<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProvider extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return ProviderResource::getUrl('index');
    }
    protected static string $resource = ProviderResource::class;
}
