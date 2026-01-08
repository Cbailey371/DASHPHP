<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_quotes',
            'export_quotes',
            'view_logistics_dashboard',
            'view_sales_dashboard',
            'manage_custom_widgets',
            'view_schema_explorer',
            'manage_sql_reports',
            'use_ai_assistant',
            'manage_ai_configuration',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Role: Gerencia (Solo puede ver cotizaciones y reportes)
        $role = Role::firstOrCreate(['name' => 'Gerencia']);
        $role->givePermissionTo([
            'view_quotes',
            'export_quotes',
            'manage_sql_reports',
            'use_ai_assistant'
        ]);

        // Role: Logistica (Ve Dashboard Logístico y Cotizaciones)
        $role = Role::firstOrCreate(['name' => 'Logistica']);
        $role->givePermissionTo(['view_logistics_dashboard', 'view_quotes']);

        // Role: Ventas (Ve Dashboard Ventas y Cotizaciones)
        $role = Role::firstOrCreate(['name' => 'Ventas']);
        $role->givePermissionTo(['view_sales_dashboard', 'view_quotes']);

        // Role: Admin (Puede hacer todo)
        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());
    }
}
