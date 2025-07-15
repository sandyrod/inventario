<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FamilyResource\Pages;
use App\Filament\Resources\FamilyResource\RelationManagers;
use App\Models\Family;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Familia'; // Singular
    protected static ?string $pluralModelLabel = 'Familias'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('familycode')
                    ->label('Codigo')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: 'families',
                        column: 'familycode',
                        ignoreRecord: true
                    )
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if (strlen($state) < 2) {
                            $set('familycode_validation_color', 'border-yellow-500');
                            $set('familycode_validation_message', 'Ingrese al menos 2 caracteres');
                            return;
                        }
                        $exists = \App\Models\Family::where('familycode', $state)
                            ->when($get('id'), fn($query, $id) => $query->where('id', '!=', $id))
                            ->exists();
                        $set('familycode_validation_color', $exists ? 'border-red-500' : 'border-green-500');
                        $set('familycode_validation_message', $exists ? 'Este código ya existe' : 'Código válido');
                    })
                    ->extraInputAttributes(fn ($get) => [
                        'class' => $get('familycode_validation_color') ?? 'border-gray-300'
                    ])
                    ->suffix(fn ($get) => 
                        strlen($get('familycode_validation_message') ?? '') > 0
                            ? new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-500">'.$get('familycode_validation_message').'</span>')
                            : null
                    ),
                Forms\Components\Hidden::make('familycode_validation_color'),
                Forms\Components\Hidden::make('familycode_validation_message'),
                Forms\Components\TextInput::make('familyname')
                    ->label('Familia')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('UA')
                    ->label('UA')
                    ->required()
                    ->options([
                        'Si' => 'Si',
                        'No' => 'No',
                    ])
                    ->default('Si') // Valor por defecto
                    ->native(false),
                Forms\Components\Select::make('matrix')
                    ->label('Matriz')
                    ->required()
                    ->options([
                        'Si' => 'Si',
                        'No' => 'No',
                    ])
                    ->default('No') // Valor por defecto
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('familycode')
                    ->label('Codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('familyname')
                    ->label('Familia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('UA')
                    ->label('UA')
                    ->searchable(),
                Tables\Columns\TextColumn::make('matrix')
                    ->label('Matriz')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stockmin')
                    ->label('Stock Minimo')
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
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
        ];
    }
}
