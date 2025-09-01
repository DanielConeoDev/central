<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Descargo extends Model
{
    protected $fillable = [
        'user_id', 'producto_codigo', 'cantidad', 'motivo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_codigo', 'codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        // ✅ Al crear un descargo -> descontar del conteo activo
        static::created(function ($descargo) {
            DB::transaction(function () use ($descargo) {
                $conteo = \App\Models\Conteo::where('producto_codigo', $descargo->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $conteo->cantidad -= $descargo->cantidad;
                    if ($conteo->cantidad < 0) $conteo->cantidad = 0;
                    $conteo->save();
                }
            });
        });

        // ✅ Al actualizar un descargo -> ajustar el conteo activo
        static::updating(function ($descargo) {
            DB::transaction(function () use ($descargo) {
                $conteo = \App\Models\Conteo::where('producto_codigo', $descargo->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $oldCantidad = $descargo->getOriginal('cantidad');
                    $newCantidad = $descargo->cantidad;

                    // Ajustar según la diferencia
                    $conteo->cantidad += $oldCantidad; // devolver lo anterior
                    $conteo->cantidad -= $newCantidad; // aplicar lo nuevo
                    if ($conteo->cantidad < 0) $conteo->cantidad = 0;
                    $conteo->save();
                }
            });
        });

        // ❌ Eliminación de descargo no modifica conteo
    }
}
