<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngresoResource\Pages;
use App\Models\Ingreso;
use App\Models\User;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;

class IngresoResource extends Resource
{
    protected static ?string $model = Ingreso::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) Ingreso::whereDate('created_at', now())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Hoy: ' . now()->format('d/m/Y');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->default(fn() => auth()->id())
                    ->required(),

                Forms\Components\TextInput::make('factura')
                    ->label('Factura')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('producto_codigo')
                    ->label('Producto')
                    ->options(function () {
                        return \App\Models\Producto::where('estado', true) // o 'activo' según tu columna
                            ->whereHas('conteos', function ($q) {
                                $q->where('activo', true);
                            })
                            ->with(['conteos' => function ($q) {
                                $q->where('activo', true);
                            }])
                            ->orderBy('nombre')
                            ->get()
                            ->mapWithKeys(function ($producto) {
                                $conteoActivo = $producto->conteos->first();
                                $cantidad = $conteoActivo ? $conteoActivo->cantidad : 0;

                                return [
                                    $producto->codigo => "{$producto->nombre} | Cant: {$cantidad}",
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),


                Forms\Components\TextInput::make('cantidad')
                    ->label('Cantidad ingresada')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario'),
                Tables\Columns\TextColumn::make('factura')->searchable(),
                Tables\Columns\TextColumn::make('producto.codigo')->label('Producto'),
                Tables\Columns\TextColumn::make('cantidad')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngresos::route('/'),
            'create' => Pages\CreateIngreso::route('/create'),
            'edit' => Pages\EditIngreso::route('/{record}/edit'),
        ];
    }
}
