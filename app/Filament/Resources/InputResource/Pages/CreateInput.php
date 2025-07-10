<?php

namespace App\Filament\Resources\InputResource\Pages;

use App\Filament\Resources\InputResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateInput extends CreateRecord
{
    protected static string $resource = InputResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Preprocesar datos si es necesario
        return $data;
    }

    protected function afterCreate(): void
    {
        // Este método se ejecuta después de la creación del registro
        DB::transaction(function () {
            $input = $this->record;
            $input->load('items.product');
            
            foreach ($input->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                    
                    
                    // Log de depuración
                    logger()->debug('Stock actualizado', [
                        'product_id' => $item->product_id,
                        'cantidad' => $item->quantity,
                        'nuevo_stock' => $item->product->stock
                    ]);
                }
            }
        });
    }
}