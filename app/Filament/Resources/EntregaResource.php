<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntregaResource\Pages;
use App\Models\Entrega;
use App\Models\User;
use App\Models\Producto;
use App\Models\Ingreso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;

use Carbon\Carbon;

class EntregaResource extends Resource
{
    protected static ?string $model = Entrega::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) Entrega::whereDate('created_at', now())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Hoy: ' . now()->format('d/m/Y');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // ----------------------------
    // 🔹 Formulario
    // ----------------------------
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos principales')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('user_id')
                                ->label('Usuario que entrega')
                                ->options(User::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->default(fn() => auth()->id())
                                ->required(),

                            Select::make('modo_factura')
                                ->label('Entrega por')
                                ->options([
                                    'manual' => 'Manual',
                                    'automatica' => 'Con factura automática',
                                    'traslado' => 'Traslado',
                                ])
                                ->reactive()
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $sede = $get('sede');
                                    if ($state === 'manual') {
                                        $set('factura', null);
                                    }
                                    if ($state === 'automatica') {
                                        $set('factura', Entrega::generarCodigoAutomatico());
                                    }
                                    if ($state === 'traslado' && $sede) {
                                        $set('factura', Entrega::generarCodigoTraslado($sede));
                                    }
                                }),
                        ]),

                        Grid::make(2)->schema([
                            // Split para Factura y Referencia lado a lado
                            Split::make([
                                TextInput::make('factura')
                                    ->label('Factura')
                                    ->reactive()
                                    ->disabled(fn($get) => $get('modo_factura') !== 'manual')
                                    ->dehydrated(true)
                                    ->required(),

                                TextInput::make('referencia')
                                    ->label('Referencia')
                                    ->placeholder('Opcional')
                                    ->nullable(),
                            ]),

                            // Select de Sede para Traslado
                            Select::make('sede')
                                ->label('Sede de traslado')
                                ->options([
                                    'ferreteria' => 'Ferretería',
                                    'zona_mar' => 'Ferretería Zona del Mar',
                                ])
                                ->reactive()
                                ->visible(fn($get) => $get('modo_factura') === 'traslado')
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($get('modo_factura') === 'traslado' && $state) {
                                        $set('factura', Entrega::generarCodigoTraslado($state));
                                    }
                                }),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('tipo_entrega')
                                ->label('Tipo de entrega')
                                ->options([
                                    'total' => 'Total',
                                    'parcial' => 'Parcial',
                                ])
                                ->default('parcial')
                                ->required()
                                ->reactive()
                                ->afterStateHydrated(function ($state, callable $set) {
                                    if ($state === 'total') {
                                        $set('estado_entrega', 'finalizada');
                                    } else {
                                        $set('estado_entrega', 'iniciada');
                                    }
                                })
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state === 'total') {
                                        $set('estado_entrega', 'finalizada');
                                    } else {
                                        $set('estado_entrega', 'iniciada');
                                    }
                                }),

                            Select::make('estado_entrega')
                                ->label('Estado de entrega')
                                ->options([
                                    'iniciada' => 'Iniciada',
                                    'finalizada' => 'Finalizada',
                                ])
                                ->required()
                                ->reactive()
                                ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                    if (!$state) {
                                        $set('estado_entrega', $get('tipo_entrega') === 'total' ? 'finalizada' : 'iniciada');
                                    }
                                })
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    if ($state === 'finalizada') {
                                        $set('tipo_entrega', 'total');
                                    } elseif ($state === 'iniciada') {
                                        $set('tipo_entrega', 'parcial');
                                    }
                                }),
                        ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Detalle de productos')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('producto_codigo')
                                        ->label('Producto')
                                        ->options(function () {
                                            return \App\Models\Producto::where('estado', true)
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
                                                        $producto->codigo => "{$producto->nombre} | Cant: {$cantidad}"
                                                    ];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required(),


                                    TextInput::make('cantidad')
                                        ->label('Cantidad entregada')
                                        ->numeric()
                                        ->required(),
                                ]),
                            ])
                            ->columns(1)
                            ->createItemButtonLabel('➕ Agregar producto')
                            ->minItems(1),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    // ----------------------------
    // 🔹 Tabla
    // ----------------------------
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('ID'),
                Tables\Columns\TextColumn::make('factura')->label('Factura'),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario')->sortable()->searchable(),

                Tables\Columns\BadgeColumn::make('tipo_entrega')
                    ->label('Tipo de entrega')
                    ->getStateUsing(fn($record) => ucfirst($record->tipo_entrega))
                    ->colors([
                        'warning' => fn($record) => $record->tipo_entrega === 'parcial',
                        'success' => fn($record) => $record->tipo_entrega === 'total',
                    ])
                    ->icons([
                        'heroicon-s-clock' => fn($record) => $record->tipo_entrega === 'parcial',
                        'heroicon-s-check' => fn($record) => $record->tipo_entrega === 'total',
                    ]),

                Tables\Columns\BadgeColumn::make('estado_entrega')
                    ->label('Estado')
                    ->getStateUsing(fn($record) => ucfirst($record->estado_entrega))
                    ->colors([
                        'warning' => fn($record) => $record->estado_entrega === 'iniciada',
                        'success' => fn($record) => $record->estado_entrega === 'finalizada',
                    ])
                    ->icons([
                        'heroicon-s-clock' => fn($record) => $record->estado_entrega === 'iniciada',
                        'heroicon-s-check' => fn($record) => $record->estado_entrega === 'finalizada',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                SelectFilter::make('tipo_entrega')
                    ->label('Tipo de entrega')
                    ->options([
                        'parcial' => 'Parcial',
                        'total' => 'Total',
                    ]),

                SelectFilter::make('estado_entrega')
                    ->label('Estado')
                    ->options([
                        'iniciada' => 'Iniciada',
                        'finalizada' => 'Finalizada',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->options(fn() => User::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // ----------------------------
    // 🔹 Páginas
    // ----------------------------
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntregas::route('/'),
            'create' => Pages\CreateEntrega::route('/create'),
            'edit' => Pages\EditEntrega::route('/{record}/edit'),
        ];
    }
}
