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
                            ->options(\App\Models\Product::all()->pluck('description', 'id'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => 
                                \App\Models\Product::where('productcode', 'like', "%{$search}%")
                                    ->orWhere('reference', 'like', "%{$search}%")
                                    ->orWhere('description', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('description', 'id'))
                            ->getOptionLabelUsing(fn ($value) => \App\Models\Product::find($value)?->description)
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
                            ->live()
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
                        'compra' => 'Compra',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferencia',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
