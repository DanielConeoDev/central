<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Conteo;

class Producto extends Model
{
    use HasFactory;

    protected $primaryKey = 'codigo';   // Clave primaria es el código
    public $incrementing = false;       // No es autoincremental
    protected $keyType = 'string';      // Tipo string

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
        'convertible',
    ];

    protected $casts = [
    'estado' => 'boolean',
    'convertible' => 'boolean',
];


    protected static function booted()
    {
        // Al crear un producto, generar automáticamente un conteo inicial en 0
        static::created(function ($producto) {
            Conteo::create([
                'producto_codigo' => $producto->codigo,
                'cantidad' => 0,
                'diferencial' => 0,
                'activo' => true,
            ]);
        });
    }


}
