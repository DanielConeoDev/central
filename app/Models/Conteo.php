<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Conteo extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_codigo',
        'cantidad',
        'diferencial',
        'activo',
        'inventario',
        'user_id',
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
        static::creating(function ($conteo) {
            // Asignar usuario actual
            $conteo->user_id = Auth::id();

            // Buscar último registro activo del producto
            $ultimo = self::where('producto_codigo', $conteo->producto_codigo)
                ->where('activo', true)
                ->latest('created_at')
                ->first();

            // Diferencial
            $conteo->diferencial = $ultimo ? ($conteo->cantidad - $ultimo->cantidad) : 0;

            // Desactivar el anterior
            if ($ultimo) {
                $ultimo->activo = false;
                $ultimo->save();
            }

            // El registro creado es siempre el principal
            $conteo->activo = true;
            $conteo->inventario = false;
        });

        static::created(function ($conteo) {
            // SOLO si no es inventario, crear copia como inventario
            if (!$conteo->inventario) {
                $inventario = $conteo->replicate(); // clona sin disparar creating
                $inventario->inventario = true;
                $inventario->activo = false;
                $inventario->saveQuietly(); // guarda sin disparar eventos
            }
        });
    }

    public static function cantidadActual($productoCodigo)
    {
        return self::where('producto_codigo', $productoCodigo)
            ->where('activo', true)
            ->value('cantidad') ?? 0;
    }
}
