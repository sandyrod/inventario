<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Marca'; // Singular
    protected static ?string $pluralModelLabel = 'Marcas'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->Label('Codigo')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: 'brands',
                        column: 'code',
                        ignoreRecord: true
                    )
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if (strlen($state) < 2) {
                            $set('code_validation_color', 'border-yellow-500');
                            $set('code_validation_message', 'Ingrese al menos 2 caracteres');
                            return;
                        }
                        $exists = \App\Models\Brand::where('code', $state)
                            ->when($get('id'), fn($query, $id) => $query->where('id', '!=', $id))
                            ->exists();
                        $set('code_validation_color', $exists ? 'border-red-500' : 'border-green-500');
                        $set('code_validation_message', $exists ? 'Este código ya existe' : 'Código válido');
                    })
                    ->extraInputAttributes(fn ($get) => [
                        'class' => $get('code_validation_color') ?? 'border-gray-300'
                    ])
                    ->suffix(fn ($get) => 
                        strlen($get('code_validation_message') ?? '') > 0
                            ? new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-500">'.$get('code_validation_message').'</span>')
                            : null
                    ),
                Forms\Components\Hidden::make('code_validation_color'),
                Forms\Components\Hidden::make('code_validation_message'),
                Forms\Components\TextInput::make('description')
                    ->Label('Descripción')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->Label('Codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->Label('Descripcion')
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('¿Estás seguro de que deseas borrar este registro?')
                    ->modalDescription('Esta acción enviará el registro a la papelera (soft delete) y podrá ser recuperado desde la base de datos.'),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
