<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    // Estos son los campos que permitiremos guardar masivamente
    protected $fillable = [
        'codigo',
        'nombre',
        'nombre_comercial',
        'tipo_cliente',     // Ej: Natural, Jurídico
        'documento',        // NIT o DUI
        'telefono',
        'correo',
        'contacto_principal',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
        'limite_credito',
        'dias_credito',
        'nit',
        'nrc',
        'exento',
        'nite',
        'pasaporte',
        'origen',
        'tipo_contribuyente',
        'id_giro',
        'estado'            // ACTIVO, INACTIVO
    ];
}
