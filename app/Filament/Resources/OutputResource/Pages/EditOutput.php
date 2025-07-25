<?php

namespace App\Filament\Resources\OutputResource\Pages;

use App\Filament\Resources\OutputResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutput extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return OutputResource::getUrl('index');
    }
    protected static string $resource = OutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
