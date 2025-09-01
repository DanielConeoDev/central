<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConteoResource\Pages;
use App\Filament\Resources\ConteoResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Models\Producto;
use App\Models\Conteo;
use Illuminate\Support\Facades\Date;
use Filament\Tables\Columns\IconColumn;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;

class ConteoResource extends Resource
{
    protected static ?string $model = Conteo::class;

    protected static ?string $navigationIcon = 'heroicon-o-hashtag';

    protected static ?int $navigationSort = 1;


    protected static ?string $navigationGroup = 'Gestión de Productos';

    public static function getNavigationBadge(): ?string
    {
        // Contar registros del día actual
        $count = Conteo::whereDate('created_at', Date::today())->count();

        // Retornar "0" si no hay registros
        return (string) $count;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Hoy: ' . Date::today()->format('d/m/Y');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // success | danger | warning | primary | secondary
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del producto')
                    ->description('Selecciona un producto y la cantidad para el conteo')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('producto_codigo')
                                    ->label('Producto')
                                    ->options(
                                        fn() =>
                                        \App\Models\Producto::where('estado', 'activo')
                                            ->orderBy('nombre')
                                            ->pluck('nombre', 'codigo')
                                    )
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Selecciona un producto'),

                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->placeholder('Ej: 10'),
                            ]),
                    ]),

                Forms\Components\Section::make('Resultado del conteo')
                    ->description('El diferencial se calcula automáticamente respecto al último registro activo')
                    ->schema([
                        Forms\Components\TextInput::make('diferencial')
                            ->label('Diferencial')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('auto')
                            ->default(fn($get) => 0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $producto = $get('producto_codigo');
                                if (!$producto) {
                                    $set('diferencial', 0);
                                    return;
                                }

                                $ultimo = \App\Models\Conteo::where('producto_codigo', $producto)
                                    ->where('activo', true)
                                    ->latest('created_at')
                                    ->first();

                                $set('diferencial', $ultimo ? ($get('cantidad') - $ultimo->cantidad) : 0);
                            }),
                    ])
                    ->collapsible()
                    ->visible(fn($record) => $record !== null)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                TextColumn::make('diferencial')
                    ->label('Diferencial')
                    ->sortable(),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->colors([
                        'success' => fn($state) => $state === true,
                        'danger' => fn($state) => $state === false,
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('producto.nombre')
                    ->label('Producto')
                    ->collapsible(),
            ])
            ->filters([
                // Filtro por producto
                SelectFilter::make('producto')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),

                // Filtro por rango de fechas
                Filter::make('created_at')
                    ->label('Rango de fechas')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q, $desde) => $q->whereDate('created_at', '>=', $desde))
                            ->when($data['hasta'], fn($q, $hasta) => $q->whereDate('created_at', '<=', $hasta));
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListConteos::route('/'),
            'create' => Pages\CreateConteo::route('/create'),
            'edit' => Pages\EditConteo::route('/{record}/edit'),
        ];
    }
}
