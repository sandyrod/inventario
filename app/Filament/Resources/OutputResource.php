<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutputResource\Pages;
use App\Filament\Resources\OutputResource\RelationManagers;
use App\Models\Output;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OutputResource extends Resource
{
    protected static ?string $model = Output::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Nota'; // Singular
    protected static ?string $pluralModelLabel = 'Notas'; // Plural

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
                        'venta' => 'Venta',
                        'deterioro' => 'Deterioro',
                        'ajuste' => 'Ajuste',
                    ])
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->options(\App\Models\Client::all()->mapWithKeys(fn ($item) => [
                                $item->id => "{$item->name} ({$item->code})"
                            ]))
                    ->getOptionLabelUsing(fn ($value) => Client::find($value)?->name . ' (' . Client::find($value)?->code . ')')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => 
                        Client::where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($client) => [
                                $client->id => $client->name . ' (' . $client->code . ')'
                            ]))
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('paymentterm_id')
                    ->label('Condicion de Pago')
                    ->relationship('paymentterm', 'name') // Asume que 'nombre' es el campo que quieres mostrar
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('paymentform_id')
                    ->label('Forma de Pago')
                    ->relationship('paymentform', 'name') // Asume que 'nombre' es el campo que quieres mostrar
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Nota')
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('amount')
                            ->label('Total Nota')
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
                                    $set('total_price', round(1 * $product->price, 2)); // Actualiza el total al cambiar producto
                                }
                            }),
                            
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->step(1)
                            ->live()
                            ->formatStateUsing(function ($state) {
                                // Muestra enteros sin decimales y decimales con 2 dígitos
                                return number_format(intval($state),0);
                            })
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $set('total_price', round($state * $get('unit_price'), 2));
                            }),
                            
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Precio unitario')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state, $get) {
                                $set('total_price', round($state * $get('quantity'), 2));
                            }),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->reactive(),
                            
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
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'venta' => 'Venta',
                        'ajuste' => 'Ajuste',
                        'deterioro' => 'Deterioro',
                    }),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto'),
                Tables\Columns\TextColumn::make('description')
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
                Tables\Filters\MultiSelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->options(function () {
                        return \App\Models\Client::query()
                            ->select(['id', 'name', 'code'])
                            ->get()
                            ->mapWithKeys(function ($client) {
                                return [
                                    $client->id => "{$client->code} - {$client->name}"
                                ];
                            });
                    })
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Client::query()
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(function ($client) {
                                return [
                                    $client->id => "{$client->code} - {$client->name}"
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
            'index' => Pages\ListOutputs::route('/'),
            'create' => Pages\CreateOutput::route('/create'),
            'edit' => Pages\EditOutput::route('/{record}/edit'),
        ];
    }
}
