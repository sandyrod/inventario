<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Imports\ProductsImport;
use App\Filament\Resources\ProductResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportProducts extends Page
{
    protected static string $resource = ProductResource::class;
    protected static string $view = 'filament.resources.product-resource.pages.import-products';

    public function getBreadcrumb(): string
    {
        return 'Importar Productos';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Excel')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel'
                        ])
                        ->preserveFilenames()
                        ->directory('imports')
                        ->visibility('public')
                        ->disk('public')
                ])
                ->action(function (array $data) {
                    try {
                        $filePath = Storage::disk('public')->path($data['file']);
                        Excel::import(new ProductsImport(), $filePath);
                        
                        Notification::make()
                            ->title('Importación exitosa')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error en la importación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}