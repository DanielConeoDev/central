<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EntregaItem extends Model
{
    protected $fillable = [
        'entrega_id',
        'producto_codigo',
        'cantidad',
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_codigo', 'codigo');
    }

    protected static function booted()
    {
        // ✅ Al crear una entrega -> descontar del conteo activo sin crear registros
        static::created(function ($item) {
            DB::transaction(function () use ($item) {
                $conteo = Conteo::where('producto_codigo', $item->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $conteo->cantidad -= $item->cantidad;
                    $conteo->save();
                }
            });
        });

        // ✅ Al actualizar -> ajustar la cantidad del conteo activo
        static::updating(function ($item) {
            DB::transaction(function () use ($item) {
                $conteo = Conteo::where('producto_codigo', $item->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $oldCantidad = $item->getOriginal('cantidad');
                    $newCantidad = $item->cantidad;

                    // Ajustar la cantidad según la diferencia
                    $conteo->cantidad += $oldCantidad; // devolver lo anterior
                    $conteo->cantidad -= $newCantidad; // aplicar lo nuevo
                    $conteo->save();
                }
            });
        });

        // ❌ Eliminación no modifica conteo
    }
}
