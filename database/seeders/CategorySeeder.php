<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Antibiotics',
            'Painkillers',
            'Antihypertensives',
            'Antidiabetics',
            'Antimalarials',
            'Antifungals',
            'Antivirals',
            'Vitamins',
            'Supplements',
            'Gastrointestinal',
            'Respiratory',
            'Steroids',
            'Anticoagulants',
            'Anticholinergics',
            'Sedatives',
            'Antipsychotics',
            'Anticonvulsants',
            'Neurological',
            'Diuretics',
        ];

        foreach ($categories as $categoryName) {
            Category::create([
                'name' => $categoryName,
            ]);
        }
    }
}