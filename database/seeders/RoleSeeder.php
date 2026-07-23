<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        // 1. Creamos los permisos (las acciones que se pueden hacer)
        Permission::create(['name' => 'ver kardex']);
        Permission::create(['name' => 'crear despacho']);
        Permission::create(['name' => 'anular orden']);

        // 2. Creamos los Roles y les asignamos sus permisos
        $admin = Role::create(['name' => 'Administrador']);
        $admin->givePermissionTo(Permission::all()); // El admin hace todo

        $bodeguero = Role::create(['name' => 'Bodeguero']);
        $bodeguero->givePermissionTo(['ver kardex', 'crear despacho']);

        $vendedor = Role::create(['name' => 'Vendedor']);
        $vendedor->givePermissionTo(['crear despacho']);
    }
}
