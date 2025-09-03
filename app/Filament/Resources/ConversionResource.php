<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversionResource\Pages;
use App\Models\Conversion;
use App\Models\Producto;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;

class ConversionResource extends Resource
{
    protected static ?string $model = Conversion::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Gestión de Productos';
    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return (string) Conversion::whereDate('created_at', now())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Hoy: ' . now()->format('d/m/Y');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Conversión')
                    ->description('Complete los datos para realizar la conversión de productos')
                    ->schema([

                        // Guardar nombre del usuario (oculto, solo lectura en DB)
                        Forms\Components\Hidden::make('user_name')
                            ->default(fn($record) => $record->user->name ?? auth()->user()->name)
                            ->disabled()
                            ->columnSpan(2),

                        // Guardar user_id automáticamente
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => auth()->id()),

                        Forms\Components\Section::make('Producto Origen')
                            ->description('Seleccione el producto que se va a descontar')
                            ->schema([
                                Forms\Components\Select::make('producto_origen')
                                    ->label('Producto Origen')
                                    ->options(function () {
                                        return \App\Models\Producto::where('estado', true)
                                            ->where('convertible', true)
                                            ->whereHas('conteos', fn($q) => $q->where('activo', true))
                                            ->with(['conteos' => fn($q) => $q->where('activo', true)])
                                            ->orderBy('nombre')
                                            ->get()
                                            ->mapWithKeys(function ($producto) {
                                                $conteoActivo = $producto->conteos->first();
                                                $cantidad = $conteoActivo ? $conteoActivo->cantidad : 0;

                                                $textoCantidad = $cantidad > 0 ? "Cant: {$cantidad}" : "AGOTADO";

                                                return [
                                                    $producto->codigo => "{$producto->nombre} | {$textoCantidad}",
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    // ✅ No puede ser igual al destino
                                    ->rule('different:producto_destino'),

                                Forms\Components\TextInput::make('cantidad_origen')
                                    ->label('Cantidad a Descontar')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Forms\Components\Section::make('Producto Destino')
                            ->description('Seleccione el producto que se va a generar')
                            ->schema([
                                Forms\Components\Select::make('producto_destino')
                                    ->label('Producto Destino')
                                    ->options(function () {
                                        return \App\Models\Producto::where('estado', true)
                                            ->where('convertible', true)
                                            ->whereHas('conteos', fn($q) => $q->where('activo', true))
                                            ->with(['conteos' => fn($q) => $q->where('activo', true)])
                                            ->orderBy('nombre')
                                            ->get()
                                            ->mapWithKeys(function ($producto) {
                                                $conteoActivo = $producto->conteos->first();
                                                $cantidad = $conteoActivo ? $conteoActivo->cantidad : 0;

                                                $textoCantidad = $cantidad > 0 ? "Cant: {$cantidad}" : "AGOTADO";

                                                return [
                                                    $producto->codigo => "{$producto->nombre} | {$textoCantidad}",
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    // ✅ No puede ser igual al origen
                                    ->rule('different:producto_origen'),

                                Forms\Components\TextInput::make('cantidad_destino')
                                    ->label('Cantidad a Generar')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),

                    ])
                    ->columns(1),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('productoOrigen.nombre')
                    ->label('Producto Origen')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cantidad_origen')
                    ->label('Cantidad Origen')
                    ->sortable(),

                Tables\Columns\TextColumn::make('productoDestino.nombre')
                    ->label('Producto Destino')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cantidad_destino')
                    ->label('Cantidad Destino')
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('usuario')
                    ->label('Usuario')
                    ->relationship('usuario', 'name'),

                Tables\Filters\SelectFilter::make('producto_origen')
                    ->label('Producto Origen')
                    ->options(Producto::all()->pluck('nombre', 'codigo')),

                Tables\Filters\SelectFilter::make('producto_destino')
                    ->label('Producto Destino')
                    ->options(Producto::all()->pluck('nombre', 'codigo')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc'); // Ordenar del más nuevo al más antiguo
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversions::route('/'),
            'create' => Pages\CreateConversion::route('/create'),
            'edit' => Pages\EditConversion::route('/{record}/edit'),
        ];
    }
}
