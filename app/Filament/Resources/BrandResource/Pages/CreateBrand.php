<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return BrandResource::getUrl('index');
    }
    protected static string $resource = BrandResource::class;

}
