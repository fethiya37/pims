<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            [
                'name' => 'Main Store',
                'type' => 'store',
            ],
            [
                'name' => 'Dispensary',
                'type' => 'point_of_use',
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}