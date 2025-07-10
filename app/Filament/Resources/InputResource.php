<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InputResource\Pages;
use App\Filament\Resources\InputResource\RelationManagers;
use App\Models\Input;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InputResource extends Resource
{
    protected static ?string $model = Input::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Entrada'; // Singular
    protected static ?string $pluralModelLabel = 'Entradas'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->default(auth()->id())
                    ->required(),
                    
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'compra' => 'Compra',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferencia',
                    ])
                    ->required(),
                Forms\Components\Select::make('provider_id')
                    ->label('Proveedor')
                    ->relationship('provider', 'name') // Asume que 'nombre' es el campo que quieres mostrar
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('dateinput')
                    ->label('Fecha del Compra o entrada')
                    ->default(now()->toDateString())
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                
                Forms\Components\DatePicker::make('datepaid')
                    ->label('Fecha de pago')
                    ->nullable()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                
                Forms\Components\Select::make('statuspaid')
                    ->label('Estado de pago')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagado' => 'Pagado',
                    ])
                    ->default('pendiente')
                    ->required()
                    ->native(false),

                Forms\Components\Textarea::make('description')
                    ->label('Nota')
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('amount')
                            ->label('Total Entrada')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->reactive(),
                    
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->options(\App\Models\Product::all()->mapWithKeys(fn ($item) => [
                                $item->id => "{$item->productcode} - {$item->description}"
                            ]))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => 
                                \App\Models\Product::where('productcode', 'like', "%{$search}%")
                                    ->orWhere('reference', 'like', "%{$search}%")
                                    ->orWhere('description', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn ($item) => [
                                        $item->id => "{$item->productcode} - {$item->description}"
                                    ]))
                            ->getOptionLabelUsing(fn ($value) => 
                                ($product = \App\Models\Product::find($value)) 
                                    ? "{$product->productcode} - {$product->description}" 
                                    : null)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $product = \App\Models\Product::find($state);
                                if ($product) {
                                    $set('unit_price', $product->price);
                                    $set('total_price', round(1 * $product->price, 2));
                                }
                            }),
                            
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->live()
                            ->formatStateUsing(function ($state) {
                                return number_format(intval($state), 0);
                            })
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $unitPrice = $get('unit_price') ?? 0;
                                $discount = $get('discount') ?? 0;
                                
                                // Calcular total con descuento
                                $totalWithDiscount = $state * $unitPrice * (1 - ($discount / 100));
                                $set('total_price', round($totalWithDiscount, 2));
                                
                                // Solo calcular ganancia si no hay descuento
                                if ($discount == 0) {
                                    $profitPercent = $get('profit_percent') ?? 0;
                                    $salesPrice = $totalWithDiscount * (1 + ($profitPercent / 100));
                                    $set('sales_price', round($salesPrice, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Costo unitario')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $quantity = $get('quantity') ?? 1;
                                $discount = $get('discount') ?? 0;
                                
                                // Calcular precio unitario con descuento
                                $unitPriceWithDiscount = $state * (1 - ($discount / 100));
                                $set('unit_price_with_discount', round($unitPriceWithDiscount, 2));
                                
                                // Calcular total con descuento
                                $totalWithDiscount = $state * $quantity * (1 - ($discount / 100));
                                $set('total_price', round($totalWithDiscount, 2));
                                
                                // Solo calcular ganancia si no hay descuento
                                if ($discount == 0) {
                                    $profitPercent = $get('profit_percent') ?? 0;
                                    $salesPrice = $totalWithDiscount * (1 + ($profitPercent / 100));
                                    $set('sales_price', round($salesPrice, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('discount')
                            ->label('Descuento (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $unitPrice = $get('unit_price') ?? 0;
                                $quantity = $get('quantity') ?? 1;
                                
                                if ($unitPrice > 0) {
                                    // Calcular precio unitario con descuento
                                    $unitPriceWithDiscount = $unitPrice * (1 - ($state / 100));
                                    $set('unit_price_with_discount', round($unitPriceWithDiscount, 2));
                                    
                                    // Calcular total con descuento
                                    $totalWithDiscount = $unitPrice * $quantity * (1 - ($state / 100));
                                    $set('total_price', round($totalWithDiscount, 2));
                                    
                                    // Manejo de estados para descuento/ganancia
                                    if ($state > 0) {
                                        $set('profit_percent', null);
                                        $set('sales_price', null);
                                    } else {
                                        // Al quitar descuento, recalcular ganancia si existe
                                        $profitPercent = $get('profit_percent') ?? 0;
                                        $salesPrice = $totalWithDiscount * (1 + ($profitPercent / 100));
                                        $set('sales_price', round($salesPrice, 2));
                                    }
                                }
                            }),

                        // Nuevo campo: Precio unitario con descuento
                        Forms\Components\TextInput::make('unit_price_with_discount')
                            ->label('Precio unitario con descuento')
                            ->numeric()
                            ->disabled() // Hacerlo de solo lectura
                            ->dehydrated()
                            ->reactive(),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total con descuento')
                            ->numeric()
                            ->dehydrated()
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\TextInput::make('profit_percent')
                            ->label('Margen de ganancia (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->default(0)
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $unitPriceWithDiscount = $get('unit_price_with_discount') ?? 0;
                                
                                if ($unitPriceWithDiscount > 0) {
                                    // Calcular sales_price aplicando el % de ganancia al precio unitario con descuento
                                    $salesPrice = $unitPriceWithDiscount * (1 + ($state / 100));
                                    $set('sales_price', round($salesPrice, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('sales_price')
                            ->label('Precio de venta con ganancia')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $unitPriceWithDiscount = $get('unit_price_with_discount') ?? 0;
                                
                                if ($unitPriceWithDiscount > 0) {
                                    // Calcular el porcentaje de ganancia basado en el sales_price y unit_price_with_discount
                                    $profitPercent = (($state - $unitPriceWithDiscount) / $unitPriceWithDiscount) * 100;
                                    $set('profit_percent', round($profitPercent, 2));
                                }
                            }),

                            
                    ])
                    ->defaultItems(1)
                    ->columns(2)
                    ->columnSpanFull()
                    ->addActionLabel('Agregar Producto')
                    ->orderable(false)
                    ->live()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        // Suma todos los total_price de los items
                        $total = collect($get('items'))
                            ->filter(fn ($item) => !empty($item['total_price']))
                            ->sum('total_price');
                        
                        $set('amount', round($total, 2));
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'compra' => 'Compra',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferencia',
                    }),
                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Proveedor'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                ->label('Tipo')
                ->options([
                    'compra' => 'Compra',
                    'ajuste' => 'Ajuste',
                    'transferencia' => 'Transferencia',
                ]),
                 Tables\Filters\SelectFilter::make('statuspaid')
                ->label('Estado de Pago')
                ->options([
                    'pendiente' => 'Pendiente',
                    'pagado' => 'Pagado',
                ]),
                Tables\Filters\MultiSelectFilter::make('provider_id')
                    ->label('Proveedor')
                    ->relationship('provider', 'name')
                    ->options(function () {
                        return \App\Models\Provider::query()
                            ->select(['id', 'name', 'code'])
                            ->get()
                            ->mapWithKeys(function ($provider) {
                                return [
                                    $provider->id => "{$provider->code} - {$provider->name}"
                                ];
                            });
                    })
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Provider::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($provider) {
                                return [
                                    $provider->id => "{$provider->code} - {$provider->name}"
                                ];
                            });
                    })
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('Desde'),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
                Tables\Filters\Filter::make('amount')
                ->form([
                    Forms\Components\TextInput::make('min_amount')
                        ->label('Monto mínimo')
                        ->numeric(),
                    Forms\Components\TextInput::make('max_amount')
                        ->label('Monto máximo')
                        ->numeric(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['min_amount'],
                            fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                        )
                        ->when(
                            $data['max_amount'],
                            fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                        );
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'); ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInputs::route('/'),
            'create' => Pages\CreateInput::route('/create'),
            'edit' => Pages\EditInput::route('/{record}/edit'),
        ];
    }
}
