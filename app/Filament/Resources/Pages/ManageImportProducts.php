<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class ManageImportProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.manage-import-product';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file')
                    ->label('Archivo Excel')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->disk('public'), // Ajusta según tu configuración
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $file = $this->form->getState()['file'] ?? null;

        if (!$file) {
            Notification::make()
                ->title('Por favor selecciona un archivo para importar.')
                ->danger()
                ->send();
            return;
        }

        try {
            Excel::import(new ProductsImport(), $file->getRealPath());

            Notification::make()
                ->title('Importación exitosa')
                ->success()
                ->send();

            $this->form->fill([]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error en la importación')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
