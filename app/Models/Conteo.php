<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conteo extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_codigo',
        'cantidad',
        'diferencial',
        'activo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_codigo', 'codigo');
    }

    protected static function booted()
    {
        static::creating(function ($conteo) {
            $ultimo = self::where('producto_codigo', $conteo->producto_codigo)
                          ->where('activo', true)
                          ->latest('created_at')
                          ->first();

            // Diferencial = 0 si no hay registro anterior
            $conteo->diferencial = $ultimo ? ($conteo->cantidad - $ultimo->cantidad) : 0;

            // Desactivar el anterior
            if ($ultimo) {
                $ultimo->activo = false;
                $ultimo->save();
            }

            $conteo->activo = true;
        });
    }

    public static function cantidadActual($productoCodigo)
    {
        return self::where('producto_codigo', $productoCodigo)
                   ->where('activo', true)
                   ->value('cantidad') ?? 0;
    }
}
