<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create([
            'role_name' => 'Super Admin',
            'superadmin' => 'on',
            'manage_user' => 'on',
            'manage_categories' => 'on',
            'manage_products' => 'on',
            'manage_locations' => 'on',
            'manage_supplier' => 'on',
            'manage_opening_quantity' => 'on',
            'manage_goods_receipt' => 'on',
            'manage_inventory_transfer' => 'on',
            'manage_inventory_adjustment' => 'on',
            'manage_patients' => 'on',
            'manage_treatment_consumption' => 'on',
            'manage_product_sales' => 'on',
            'view_reports' => 'on',
        ]);

        Role::create([
            'role_name' => 'Dispensary Manager',
            'superadmin' => 'off',
            'manage_user' => 'off',
            'manage_categories' => 'off',
            'manage_products' => 'off',
            'manage_locations' => 'off',
            'manage_supplier' => 'off',
            'manage_opening_quantity' => 'off',
            'manage_goods_receipt' => 'off',
            'manage_inventory_transfer' => 'on',
            'manage_inventory_adjustment' => 'off',
            'manage_patients' => 'off',
            'manage_treatment_consumption' => 'on',
            'manage_product_sales' => 'on',
            'view_reports' => 'on',
        ]);

        Role::create([
            'role_name' => 'Main Store Manager',
            'superadmin' => 'off',
            'manage_user' => 'off',
            'manage_categories' => 'off',
            'manage_products' => 'off',
            'manage_locations' => 'off',
            'manage_supplier' => 'on',
            'manage_opening_quantity' => 'on',
            'manage_goods_receipt' => 'on',
            'manage_inventory_transfer' => 'on',
            'manage_inventory_adjustment' => 'off',
            'manage_patients' => 'off',
            'manage_treatment_consumption' => 'off',
            'manage_product_sales' => 'off',
            'view_reports' => 'on',
        ]);
    }
}