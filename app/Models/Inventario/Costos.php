<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Costos extends Model
{
    use SoftDeletes;

    protected $table = 'costos';
    protected $primaryKey = 'id_costo';

    public $timestamps = true; // created_at y updated_at

    protected $fillable = [
        'nombre',
        'descripcion',
        'inactivo'
    ];

    protected $dates = ['deleted_at']; // ← necesario para SoftDeletes
}
