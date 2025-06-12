<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Producto'; // Singular
    protected static ?string $pluralModelLabel = 'Productos'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('productcode')
                    ->label('Código')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('reference')
                    ->label('Referencia')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('family_id')
                    ->label('Familia')
                    ->required()
                    ->relationship('family', 'familyname') // 'name' es el campo a mostrar
                    ->searchable() // Opcional: permite búsqueda
                    ->preload(),
                Forms\Components\Select::make('brand_id')
                    ->label('Marca')
                    ->required()
                    ->relationship('brand', 'description') // 'name' es el campo a mostrar
                    ->searchable() // Opcional: permite búsqueda
                    ->preload(),
                Forms\Components\Select::make('unit_id')
                    ->label('Unidad')
                    ->required()
                    ->relationship('unit', 'unitname') // 'name' es el campo a mostrar
                    ->searchable() // Opcional: permite búsqueda
                    ->preload(),
                Forms\Components\TextInput::make('cost')
                    ->label('Costo USD')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('stock')
                    ->label('Stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('stonkmin')
                    ->label('Stock Minimo')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->headerActions([
                
            
                Action::make('import') // Ahora Action está correctamente importada
                    ->label('Importar Productos')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->url(fn () => static::getUrl('import'))
                    ->button(),
        ])
            ->columns([
                Tables\Columns\TextColumn::make('productcode')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('family.familyname')
                    ->label('Familia')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.description')
                    ->label('Marca')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unitname')
                    ->label('Unidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Costo')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stonkmin')
                    ->label('Stock Minimo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro por Stock Mínimo
                Filter::make('stockmin_alert')
                    ->label('Stock bajo mínimo')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock', '<', 'stonkmin')),
                
                // Filtro por Rango de Precio
                Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->label('Precio mínimo')
                            ->numeric(),
                        Forms\Components\TextInput::make('max')
                            ->label('Precio máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'],
                                fn (Builder $query, $min): Builder => $query->where('price', '>=', $min))
                            ->when($data['max'],
                                fn (Builder $query, $max): Builder => $query->where('price', '<=', $max));
                    }),
                
                // Filtro por Rango de Stock
                Filter::make('stock_range')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->label('Stock mínimo')
                            ->numeric(),
                        Forms\Components\TextInput::make('max')
                            ->label('Stock máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'],
                                fn (Builder $query, $min): Builder => $query->where('stock', '>=', $min))
                            ->when($data['max'],
                                fn (Builder $query, $max): Builder => $query->where('stock', '<=', $max));
                    }),
                
                // Filtro por Familia
                SelectFilter::make('family_id')
                    ->label('Familia')
                    ->relationship('family', 'familyname')
                    ->searchable()
                    ->preload(),
                
                // Filtro por Marca
                SelectFilter::make('brand_id')
                    ->label('Marca')
                    ->relationship('brand', 'description')
                    ->searchable()
                    ->preload(),
                
                // Filtro por Unidad
                SelectFilter::make('unit_id')
                    ->label('Unidad')
                    ->relationship('unit', 'unitname')
                    ->searchable()
                    ->preload(),
        
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('import')
                // ->label('Importar')
                // ->url(fn () => static::getUrl('import'))
                // ->icon('heroicon-o-arrow-up-tray')
                // ->color('warning') // Color naranja
                // ->size('md'), // Tamaño mediano
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'import' => Pages\ImportProducts::route('/import'),
        ];
    }
}
