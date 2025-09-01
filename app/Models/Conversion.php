<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Conversion extends Model
{
    protected $table = 'conversiones';
    protected $fillable = [
        'user_id', 'producto_origen', 'producto_destino', 'cantidad_origen', 'cantidad_destino',
    ];

    // Relaciones
    public function productoOrigen()
    {
        return $this->belongsTo(Producto::class, 'producto_origen', 'codigo');
    }

    public function productoDestino()
    {
        return $this->belongsTo(Producto::class, 'producto_destino', 'codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        // ✅ Al crear una conversión -> ajustar conteo de productos
        static::created(function ($conversion) {
            DB::transaction(function () use ($conversion) {
                // Descontar del producto origen
                $origen = \App\Models\Conteo::where('producto_codigo', $conversion->producto_origen)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($origen) {
                    $origen->cantidad -= $conversion->cantidad_origen;
                    if ($origen->cantidad < 0) $origen->cantidad = 0;
                    $origen->save();
                }

                // Sumar al producto destino
                $destino = \App\Models\Conteo::where('producto_codigo', $conversion->producto_destino)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($destino) {
                    $destino->cantidad += $conversion->cantidad_destino;
                    $destino->save();
                }
            });
        });

        // ✅ Al actualizar una conversión -> ajustar diferencias
        static::updating(function ($conversion) {
            DB::transaction(function () use ($conversion) {
                $oldOrigenCantidad = $conversion->getOriginal('cantidad_origen');
                $oldDestinoCantidad = $conversion->getOriginal('cantidad_destino');

                // Ajustar producto origen
                $origen = \App\Models\Conteo::where('producto_codigo', $conversion->producto_origen)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($origen) {
                    $origen->cantidad += $oldOrigenCantidad; // devolver lo antiguo
                    $origen->cantidad -= $conversion->cantidad_origen; // restar lo nuevo
                    if ($origen->cantidad < 0) $origen->cantidad = 0;
                    $origen->save();
                }

                // Ajustar producto destino
                $destino = \App\Models\Conteo::where('producto_codigo', $conversion->producto_destino)
                    ->where('activo', true)
                    ->lockForUpdate()
                    ->first();

                if ($destino) {
                    $destino->cantidad -= $oldDestinoCantidad; // quitar lo antiguo
                    $destino->cantidad += $conversion->cantidad_destino; // sumar lo nuevo
                    $destino->save();
                }
            });
        });

        // ❌ Al eliminar no hacemos cambios en conteo (si quieres, se puede agregar)
    }
}
