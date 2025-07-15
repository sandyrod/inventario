<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderResource\Pages;
use App\Filament\Resources\ProviderResource\RelationManagers;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Proveedor'; // Singular
    protected static ?string $pluralModelLabel = 'Proveedores'; // Plural

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('code')
                ->label('Cédula o Rif')
                ->required()
                ->maxLength(255)
                ->unique(
                    table: 'providers',
                    column: 'code',
                    ignoreRecord: true
                )
                ->live(debounce: 500)
                ->afterStateUpdated(function ($state, $set, $get) {
                    if (strlen($state) < 6) {
                        $set('code_validation_color', 'border-yellow-500');
                        $set('code_validation_message', 'Ingrese al menos 6 caracteres');
                        return;
                    }

                    $exists = \App\Models\Provider::where('code', $state)
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
                        ? new HtmlString('<span class="text-xs text-gray-500">'.$get('code_validation_message').'</span>')
                        : null
                ),
                
            // Agrega estos campos ocultos al esquema
            Forms\Components\Hidden::make('code_validation_color'),
            Forms\Components\Hidden::make('code_validation_message'),
            Forms\Components\TextInput::make('name')
                ->label('Nombre o Razón Social')
                ->required()
                ->maxLength(255),
                
            Forms\Components\TextInput::make('phone')
                ->label('Teléfono')
                ->tel()
                ->maxLength(255),
                
            Forms\Components\Textarea::make('address')
                ->label('Dirección')
                ->columnSpanFull(),
                
            Forms\Components\TextInput::make('email')  // Cambiado de Textarea a TextInput para email
                ->label('Email')
                ->email()
                ->columnSpanFull(),
                
            Forms\Components\TextInput::make('web')
                ->label('Sitio Web')
                ->columnSpanFull(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Cédula o Rif')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre o Razón Social')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
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
            'index' => Pages\ListProviders::route('/'),
            'create' => Pages\CreateProvider::route('/create'),
            'edit' => Pages\EditProvider::route('/{record}/edit'),
        ];
    }
}
