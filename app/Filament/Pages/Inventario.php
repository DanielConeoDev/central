<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use App\Models\Conteo;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Grouping\Group;

class Inventario extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationGroup = 'Reportes';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Inventario';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static string $view = 'filament.pages.inventario';
    protected static ?string $slug = 'Inventario';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_Inventario');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Conteo::query()
                    ->with('producto')
                    ->join('productos', 'conteos.producto_codigo', '=', 'productos.codigo')
                    ->orderBy('productos.categoria', 'asc')
                    ->orderBy('productos.nombre', 'asc')
                    ->select('conteos.*') // importante para que Filament funcione bien
            )
            ->columns([
                TextColumn::make('producto.codigo')
                    ->label('Código')
                    ->searchable(),

                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable(),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                BadgeColumn::make('nivel_stock')
                    ->label('Nivel de Stock')
                    ->getStateUsing(function (Conteo $record) {
                        $producto = $record->producto;
                        if (!$producto) return 'N/A';

                        $cantidad = (int) $record->cantidad;
                        $min = (int) ($producto->cantidad_min ?? 0);
                        $max = (int) ($producto->cantidad_max ?? PHP_INT_MAX);

                        if ($cantidad <= 0) {
                            return 'AGOTADO';
                        } elseif ($cantidad <= $min) {
                            return 'BAJO';
                        } elseif ($cantidad >= $max) {
                            return 'ALTO';
                        } else {
                            return 'NORMAL';
                        }
                    })
                    ->icon(fn($state) => match ($state) {
                        'AGOTADO' => 'heroicon-o-x-circle',
                        'BAJO'    => 'heroicon-o-exclamation-circle',
                        'ALTO'    => 'heroicon-o-check-circle',
                        'NORMAL'  => 'heroicon-o-minus-circle',
                        default   => null,
                    })
                    ->colors([
                        'danger' => fn($state) => in_array($state, ['AGOTADO', 'BAJO']),
                        'success' => fn($state) => $state === 'ALTO',
                        'warning' => fn($state) => $state === 'NORMAL',
                    ])
                    ->sortable(),

                TextColumn::make('diferencial')
                    ->label('Diferencial')
                    ->sortable(),

                IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('producto_codigo')
                    ->label('Productos')
                    ->multiple()
                    ->options(Producto::orderBy('nombre')->pluck('nombre', 'codigo')->toArray())
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values'])) {
                            $query->whereIn('producto_codigo', $data['values']);
                        } else {
                            $query->where('activo', true);
                        }
                    }),

                Filter::make('fecha')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) {
                            $query->whereDate('conteos.created_at', '>=', $data['desde']);
                        }
                        if ($data['hasta']) {
                            $query->whereDate('conteos.created_at', '<=', $data['hasta']);
                        }
                    }),
            ])
            ->groups([
                Group::make('producto.categoria')
                    ->label('Categoría')
                    ->collapsible(),
            ])
            ->defaultGroup('producto.categoria');
    }

    protected function getTableRecordUrl($record): ?string
    {
        return route('filament.resources.productos.edit', $record->producto_id);
    }
}
