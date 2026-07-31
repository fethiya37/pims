<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $superAdminRole = Role::where('role_name', 'Super Admin')->first();
        $dispensaryManagerRole = Role::where('role_name', 'Dispensary Manager')->first();
        $mainStoreManagerRole = Role::where('role_name', 'Main Store Manager')->first();

        $mainStore = Location::where('name', 'Main Store')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'role_id' => $superAdminRole->id,
            'is_active' => true,
            'phone' => '0911027667',
            'location_id' => $mainStore->id ?? null,
        ]);

       
        

      
    }
}