<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Unidad'; // Singular
    protected static ?string $pluralModelLabel = 'Unidades'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unitname')
                    ->label('Unidad')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: 'units',
                        column: 'unitname',
                        ignoreRecord: true
                    )
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if (strlen($state) < 2) {
                            $set('unitname_validation_color', 'border-yellow-500');
                            $set('unitname_validation_message', 'Ingrese al menos 2 caracteres');
                            return;
                        }
                        $exists = \App\Models\Unit::where('unitname', $state)
                            ->when($get('id'), fn($query, $id) => $query->where('id', '!=', $id))
                            ->exists();
                        $set('unitname_validation_color', $exists ? 'border-red-500' : 'border-green-500');
                        $set('unitname_validation_message', $exists ? 'Este nombre ya existe' : 'Nombre válido');
                    })
                    ->extraInputAttributes(fn ($get) => [
                        'class' => $get('unitname_validation_color') ?? 'border-gray-300'
                    ])
                    ->suffix(fn ($get) => 
                        strlen($get('unitname_validation_message') ?? '') > 0
                            ? new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-500">'.$get('unitname_validation_message').'</span>')
                            : null
                    ),
                Forms\Components\Hidden::make('unitname_validation_color'),
                Forms\Components\Hidden::make('unitname_validation_message'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unitname')
                    ->label('Unidad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Fecha de borrado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ultima modificación')
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
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
