<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\FileUpload;

class ImportProductResource extends Resource
{
    protected static ?string $model = null;
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationLabel = 'Importar Productos';
    protected static ?string $navigationGroup = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file')
                    ->label('Archivo Excel')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ])
                    ->maxSize(1024)
                    ->directory('imports')
                    ->preserveFilenames()
                    ->visibility('private'),
            ])
            ->statePath('data');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageImportProducts::route('/'),
        ];
    }
}