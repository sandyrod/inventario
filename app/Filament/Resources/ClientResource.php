<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Cliente'; // Singular
    protected static ?string $pluralModelLabel = 'Clientes'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Cedula o Rif')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        table: 'clients', // Nombre de la tabla en la BD
                        column: 'code',
                        ignoreRecord: true // Esto permite ignorar el registro actual al editar
                    )
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if (strlen($state) < 6) {
                            $set('code_validation_color', 'border-yellow-500');
                            $set('code_validation_message', 'Ingrese al menos 6 caracteres');
                            return;
                        }

                        $exists = \App\Models\Client::where('code', $state)
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
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Cedula o Rif')
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
