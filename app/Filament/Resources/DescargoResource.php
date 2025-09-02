<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DescargoResource\Pages;
use App\Models\Descargo;
use App\Models\User;
use App\Models\Producto;
use App\Models\Conteo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class DescargoResource extends Resource
{
    protected static ?string $model = Descargo::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationGroup = 'Gestión de Productos';
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) Descargo::whereDate('created_at', now())->count();
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
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->default(fn() => auth()->id())
                    ->required(),

                Forms\Components\Select::make('producto_codigo')
                    ->label('Producto')
                    ->options(Producto::where('estado', 'activo')
                        ->orderBy('nombre')
                        ->pluck('nombre', 'codigo'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->required()
                    ->rule(function ($get) {
                        $productoCodigo = $get('producto_codigo');
                        $conteo = Conteo::where('producto_codigo', $productoCodigo)
                            ->where('activo', true)
                            ->first();
                        return $conteo ? 'max:' . $conteo->cantidad : '';
                    }),

                Forms\Components\Textarea::make('motivo')
                    ->label('Motivo del descargo')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDescargos::route('/'),
            'create' => Pages\CreateDescargo::route('/create'),
            'edit' => Pages\EditDescargo::route('/{record}/edit'),
        ];
    }

    protected static function booted()
    {
        // Actualizar el conteo al crear un descargo
        static::created(function ($descargo) {
            DB::transaction(function () use ($descargo) {
                $conteo = Conteo::where('producto_codigo', $descargo->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $conteo->cantidad -= $descargo->cantidad;
                    $conteo->save();
                }
            });
        });

        // Ajustar conteo al actualizar la cantidad
        static::updating(function ($descargo) {
            DB::transaction(function () use ($descargo) {
                $conteo = Conteo::where('producto_codigo', $descargo->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $oldCantidad = $descargo->getOriginal('cantidad');
                    $newCantidad = $descargo->cantidad;
                    $conteo->cantidad += $oldCantidad; // devolver anterior
                    $conteo->cantidad -= $newCantidad; // restar nuevo
                    $conteo->save();
                }
            });
        });
    }
}
