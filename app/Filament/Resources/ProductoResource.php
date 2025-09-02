<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Gestión de Productos';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) Producto::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected static ?string $navigationBadgeTooltip = 'Productos Creados';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                    ->schema([
                        Split::make([
                            Section::make('Información del producto')
                                ->schema([
                                    TextInput::make('codigo')
                                        ->label('Código')
                                        ->placeholder('Ej: PROD-001')
                                        ->required()
                                        ->maxLength(50)
                                        ->unique(ignoreRecord: true),

                                    TextInput::make('nombre')
                                        ->label('Nombre del producto')
                                        ->placeholder('Ej: Nombre Producto')
                                        ->required()
                                        ->maxLength(255),

                                    Textarea::make('descripcion')
                                        ->label('Descripción detallada')
                                        ->placeholder('Opcional: características, detalles, material, etc.')
                                        ->maxLength(1000),
                                ])
                                ->grow(),

                            Section::make('Configuración')
                                ->schema([
                                    Toggle::make('estado')
                                        ->label('Activo')
                                        ->onIcon('heroicon-o-check-circle')
                                        ->offIcon('heroicon-o-x-circle')
                                        ->default(true),

                                    Toggle::make('convertible')
                                        ->label('¿Convertible?')
                                        ->onIcon('heroicon-o-check-circle')
                                        ->offIcon('heroicon-o-x-circle')
                                        ->default(false),
                                ])
                                ->grow(false),
                        ])->from('md'),
                    ])
                    ->columns(1) // el Section principal cubre todo el ancho
                    ->columnSpanFull(), // asegura que en desktop ocupe la pantalla completa
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                // Estado booleano con íconos
                Tables\Columns\IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->sortable(),

                // Convertible booleano con íconos
                Tables\Columns\IconColumn::make('convertible')
                    ->label('Convertible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->colors([
                        'success' => true,
                        'secondary' => false,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
