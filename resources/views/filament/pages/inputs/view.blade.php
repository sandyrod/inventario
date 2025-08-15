<div class="space-y-6">
    <!-- Header Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Información de la Entrada</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Número de Entrada</p>
                <p class="text-gray-900">#{{ $record->id }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Tipo</p>
                <p class="text-gray-900">
                    @switch($record->type)
                        @case('compra')
                            Compra
                            @break
                        @case('ajuste')
                            Ajuste
                            @break
                        @case('transferencia')
                            Transferencia
                            @break
                        @default
                            {{ $record->type }}
                    @endswitch
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Fecha</p>
                <p class="text-gray-900">{{ $record->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Proveedor</p>
                <p class="text-gray-900">{{ $record->provider->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Estado de Pago</p>
                <p class="text-gray-900">
                    {{ $record->statuspaid === 'pagado' ? 'Pagado' : 'Pendiente' }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total</p>
                <p class="text-lg font-semibold text-gray-900">${{ number_format($record->amount, 2) }}</p>
            </div>
            @if($record->description)
                <div class="md:col-span-3">
                    <p class="text-sm font-medium text-gray-500">Notas</p>
                    <p class="text-gray-900">{{ $record->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Productos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Costo Unitario</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Venta</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Costo Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->product->productcode ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $item->product->description ?? 'Producto no encontrado' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                {{ number_format($item->quantity, 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                ${{ number_format($item->unit_price_with_discount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                ${{ number_format($item->sales_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                ${{ number_format($item->total_price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase">
                            Total General
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900">
                            ${{ number_format($record->amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="flex justify-end space-x-3">
        <x-filament::button wire:click="$emit('closeModal')" color="gray">
            Cerrar
        </x-filament::button>
    </div>
</div>
