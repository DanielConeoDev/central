<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'cantidad_min',
        'cantidad_max',
        'categoria',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'convertible' => 'boolean',
        'cantidad_min' => 'integer',
        'cantidad_max' => 'integer',
    ];

    public function conteos()
    {
        return $this->hasMany(Conteo::class, 'producto_codigo', 'codigo');
    }
}
