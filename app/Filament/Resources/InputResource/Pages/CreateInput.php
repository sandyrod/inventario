<?php

namespace App\Filament\Resources\InputResource\Pages;

use App\Filament\Resources\InputResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateInput extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return InputResource::getUrl('index');
    }
    protected static string $resource = InputResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si existen items, aseguramos que unit_price_with_discount nunca sea null
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $k => $item) {
                if (!isset($item['unit_price_with_discount']) || is_null($item['unit_price_with_discount'])) {
                    $data['items'][$k]['unit_price_with_discount'] = 0;
                }
            }
        }
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
                    // Actualizar cost (precio de costo con descuento)
                    if (isset($item->unit_price_with_discount)) {
                        $item->product->cost = $item->unit_price_with_discount;
                    }
                    
                    // Actualizar price (precio de venta)
                    if (isset($item->sales_price)) {
                        $item->product->price = $item->sales_price;
                    }
                    
                    // Guardar todos los cambios del producto
                    $item->product->save();
                    
                    // Log de depuración
                    logger()->debug('Producto actualizado', [
                        'product_id' => $item->product_id,
                        'cantidad_agregada' => $item->quantity,
                        'nuevo_stock' => $item->product->stock,
                        'nuevo_costo' => $item->unit_price_with_discount ?? null,
                        'nuevo_precio' => $item->sales_price ?? null
                    ]);
                }
            }
        });
    }
}