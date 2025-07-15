<?php

namespace App\Filament\Resources\InputResource\Pages;

use App\Filament\Resources\InputResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInput extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return InputResource::getUrl('index');
    }
    protected static string $resource = InputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
