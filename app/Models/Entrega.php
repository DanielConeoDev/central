<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Entrega extends Model
{
    protected $fillable = ['user_id', 'factura', 'tipo_entrega', 'estado_entrega'];

    // ----------------------------
    // 🔹 Relaciones
    // ----------------------------
    public function items(): HasMany
    {
        return $this->hasMany(EntregaItem::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ----------------------------
    // 🔹 Generación de códigos
    // ----------------------------

    /**
     * Generar código manual (valor ingresado por el usuario).
     */
    public static function generarCodigoManual(string $valor): string
    {
        return strtoupper(trim($valor));
    }

    /**
     * Generar código automático con prefijo AUTO.
     */
    public static function generarCodigoAutomatico(): string
    {
        $ultimo = self::where('factura', 'like', 'AUTO-%')->latest('id')->first();
        $numero = $ultimo ? ((int) str_replace('AUTO-', '', $ultimo->factura)) + 1 : 1;

        return 'AUTO-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generar código consecutivo por traslado según sede.
     */
    public static function generarCodigoTraslado(string $sede): string
    {
        $prefijos = [
            'ferreteria' => 'FER',
            'zona_mar'   => 'ZMAR',
        ];

        $prefijo = $prefijos[$sede] ?? 'SED';

        $ultimo = self::where('factura', 'like', $prefijo . '-%')->latest('id')->first();
        $numero = $ultimo ? ((int) str_replace($prefijo . '-', '', $ultimo->factura)) + 1 : 1;

        return $prefijo . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
