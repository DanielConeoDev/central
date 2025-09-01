<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ingreso extends Model
{
    protected $fillable = [
        'user_id', 'factura', 'producto_codigo', 'cantidad',
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
        // ✅ Al crear un ingreso -> sumar cantidad al conteo activo
        static::created(function ($ingreso) {
            DB::transaction(function () use ($ingreso) {
                $conteo = Conteo::where('producto_codigo', $ingreso->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $conteo->cantidad += $ingreso->cantidad;
                    $conteo->save();
                }
            });
        });

        // ✅ Al actualizar un ingreso -> ajustar cantidad del conteo activo
        static::updating(function ($ingreso) {
            DB::transaction(function () use ($ingreso) {
                $conteo = Conteo::where('producto_codigo', $ingreso->producto_codigo)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($conteo) {
                    $oldCantidad = $ingreso->getOriginal('cantidad');
                    $newCantidad = $ingreso->cantidad;

                    // Ajustar la cantidad según la diferencia
                    $conteo->cantidad -= $oldCantidad; // quitar cantidad anterior
                    $conteo->cantidad += $newCantidad; // sumar nueva cantidad
                    $conteo->save();
                }
            });
        });

        // ❌ Eliminación no modifica conteo
    }
}
