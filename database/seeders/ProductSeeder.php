<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Please seed categories first.');
            return;
        }

        $this->command->info('Seeding 1000 products...');
        $bar = $this->command->getOutput()->createProgressBar(1000);
        $bar->start();

        $productData = [
            // Base product names with variations
            ['Amoxicillin', ['mg' => ['250', '500', '875'], 'form' => ['Capsule', 'Tablet']]],
            ['Ciprofloxacin', ['mg' => ['250', '500', '750'], 'form' => ['Tablet']]],
            ['Azithromycin', ['mg' => ['250', '500'], 'form' => ['Tablet', 'Capsule']]],
            ['Doxycycline', ['mg' => ['50', '100'], 'form' => ['Capsule']]],
            ['Clarithromycin', ['mg' => ['250', '500'], 'form' => ['Tablet']]],
            ['Metronidazole', ['mg' => ['250', '400'], 'form' => ['Tablet']]],
            ['Paracetamol', ['mg' => ['500', '1000'], 'form' => ['Tablet']]],
            ['Ibuprofen', ['mg' => ['200', '400', '600'], 'form' => ['Tablet']]],
            ['Diclofenac', ['mg' => ['50', '75'], 'form' => ['Tablet']]],
            ['Tramadol', ['mg' => ['50', '100'], 'form' => ['Capsule']]],
            ['Morphine', ['mg' => ['10', '30'], 'form' => ['Tablet']]],
            ['Codeine', ['mg' => ['15', '30', '60'], 'form' => ['Tablet']]],
            ['Aspirin', ['mg' => ['100', '300', '500'], 'form' => ['Tablet']]],
            ['Naproxen', ['mg' => ['250', '500'], 'form' => ['Tablet']]],
            ['Amlodipine', ['mg' => ['2.5', '5', '10'], 'form' => ['Tablet']]],
            ['Lisinopril', ['mg' => ['5', '10', '20'], 'form' => ['Tablet']]],
            ['Losartan', ['mg' => ['25', '50', '100'], 'form' => ['Tablet']]],
            ['Enalapril', ['mg' => ['5', '10'], 'form' => ['Tablet']]],
            ['Metformin', ['mg' => ['500', '750', '1000'], 'form' => ['Tablet']]],
            ['Glibenclamide', ['mg' => ['2.5', '5'], 'form' => ['Tablet']]],
            ['Gliclazide', ['mg' => ['30', '60', '80'], 'form' => ['Tablet']]],
            ['Omeprazole', ['mg' => ['10', '20', '40'], 'form' => ['Capsule']]],
            ['Pantoprazole', ['mg' => ['20', '40'], 'form' => ['Tablet']]],
            ['Ranitidine', ['mg' => ['150', '300'], 'form' => ['Tablet']]],
            ['Ceftriaxone', ['mg' => ['250', '500', '1000'], 'form' => ['Injection']]],
            ['Ampicillin', ['mg' => ['250', '500'], 'form' => ['Capsule']]],
            ['Levofloxacin', ['mg' => ['250', '500'], 'form' => ['Tablet']]],
            ['Moxifloxacin', ['mg' => ['400'], 'form' => ['Tablet']]],
            ['Cefuroxime', ['mg' => ['250', '500'], 'form' => ['Tablet']]],
            ['Cefotaxime', ['mg' => ['500', '1000'], 'form' => ['Injection']]],
            ['Gentamicin', ['mg' => ['40', '80'], 'form' => ['Injection']]],
            ['Vancomycin', ['mg' => ['250', '500'], 'form' => ['Injection']]],
            ['Artemether', ['mg' => ['20', '40'], 'form' => ['Tablet']]],
            ['Lumefantrine', ['mg' => ['120'], 'form' => ['Tablet']]],
            ['Quinine', ['mg' => ['300', '600'], 'form' => ['Tablet']]],
            ['Chloroquine', ['mg' => ['150', '250'], 'form' => ['Tablet']]],
            ['Primaquine', ['mg' => ['15'], 'form' => ['Tablet']]],
            ['Fluconazole', ['mg' => ['50', '100', '150'], 'form' => ['Capsule']]],
            ['Ketoconazole', ['mg' => ['200'], 'form' => ['Tablet']]],
            ['Terbinafine', ['mg' => ['250'], 'form' => ['Tablet']]],
            ['Acyclovir', ['mg' => ['200', '400', '800'], 'form' => ['Tablet']]],
            ['Zidovudine', ['mg' => ['100', '300'], 'form' => ['Tablet']]],
            ['Lamivudine', ['mg' => ['150', '300'], 'form' => ['Tablet']]],
            ['Efavirenz', ['mg' => ['200', '400', '600'], 'form' => ['Tablet']]],
            ['Tenofovir', ['mg' => ['300'], 'form' => ['Tablet']]],
            ['Nevirapine', ['mg' => ['200'], 'form' => ['Tablet']]],
            ['Oseltamivir', ['mg' => ['75'], 'form' => ['Capsule']]],
            ['Valacyclovir', ['mg' => ['500', '1000'], 'form' => ['Tablet']]],
            ['Multivitamin', ['mg' => [], 'form' => ['Tablet', 'Capsule']]],
            ['Vitamin C', ['mg' => ['500', '1000'], 'form' => ['Tablet']]],
            ['Vitamin D', ['mg' => ['1000'], 'form' => ['Capsule']]],
            ['Vitamin B12', ['mg' => ['1000'], 'form' => ['Tablet']]],
            ['Iron', ['mg' => ['100', '200', '300'], 'form' => ['Tablet']]],
            ['Folic Acid', ['mg' => ['1', '5'], 'form' => ['Tablet']]],
            ['Calcium', ['mg' => ['500', '600', '1000'], 'form' => ['Tablet']]],
            ['Zinc', ['mg' => ['10', '25', '50'], 'form' => ['Tablet']]],
            ['Magnesium', ['mg' => ['200', '250', '400'], 'form' => ['Tablet']]],
            ['Omega-3', ['mg' => ['1000'], 'form' => ['Capsule']]],
            ['Prednisolone', ['mg' => ['5', '10', '20'], 'form' => ['Tablet']]],
            ['Dexamethasone', ['mg' => ['2', '4', '8'], 'form' => ['Tablet']]],
            ['Hydrocortisone', ['mg' => ['1%', '2.5%'], 'form' => ['Cream']]],
            ['Betamethasone', ['mg' => ['0.1%', '0.5%'], 'form' => ['Cream']]],
            ['Warfarin', ['mg' => ['1', '2', '5'], 'form' => ['Tablet']]],
            ['Heparin', ['mg' => ['5000'], 'form' => ['Injection']]],
            ['Atropine', ['mg' => ['0.5', '1'], 'form' => ['Injection']]],
            ['Epinephrine', ['mg' => ['0.5', '1'], 'form' => ['Injection']]],
            ['Diazepam', ['mg' => ['2', '5', '10'], 'form' => ['Tablet']]],
            ['Lorazepam', ['mg' => ['0.5', '1', '2'], 'form' => ['Tablet']]],
            ['Haloperidol', ['mg' => ['2', '5', '10'], 'form' => ['Tablet']]],
            ['Carbamazepine', ['mg' => ['100', '200', '400'], 'form' => ['Tablet']]],
            ['Phenytoin', ['mg' => ['100', '300'], 'form' => ['Tablet']]],
            ['Levodopa', ['mg' => ['250', '500'], 'form' => ['Tablet']]],
            ['Furosemide', ['mg' => ['20', '40'], 'form' => ['Tablet']]],
            ['Spironolactone', ['mg' => ['25', '50'], 'form' => ['Tablet']]],
            ['Metoclopramide', ['mg' => ['5', '10'], 'form' => ['Tablet']]],
            ['Loperamide', ['mg' => ['2'], 'form' => ['Capsule']]],
            ['Bisacodyl', ['mg' => ['5'], 'form' => ['Tablet']]],
            ['Salbutamol', ['mg' => ['2', '4'], 'form' => ['Tablet']]],
        ];

        $categoryNames = $categories->pluck('name')->toArray();
        $counter = 0;
        $products = [];

        // Generate 1000 products by creating variations
        for ($i = 0; $i < 1000; $i++) {
            // Pick random base product
            $baseProduct = $productData[array_rand($productData)];
            $name = $baseProduct[0];
            $options = $baseProduct[1];
            
            // Determine strength
            if (!empty($options['mg'])) {
                $strength = $options['mg'][array_rand($options['mg'])];
                $productName = $name . ' ' . $strength . 'mg';
            } else {
                $productName = $name;
            }
            
            // Determine form
            $form = $options['form'][array_rand($options['form'])];
            
            // Determine packaging type
            $packagingType = $this->getPackagingType($form);
            
            // Determine pack size based on form
            $packSize = $this->getPackSize($form);
            
            // Determine unit
            $unit = $this->getUnit($form);
            
            // Get random category
            $categoryName = $categoryNames[array_rand($categoryNames)];
            $category = Category::where('name', $categoryName)->first();
            
            if (!$category) {
                $category = $categories->first();
            }
            
            // Randomly add suffix for uniqueness
            $suffix = rand(1, 99);
            $finalName = $productName . ' ' . $suffix;
            
            $products[] = [
                'name' => $finalName,
                'category_id' => $category->id,
                'description' => $this->generateDescription($finalName, $form),
                'packaging_type' => $packagingType,
                'default_pack_size' => $packSize,
                'unit' => $unit,
                'item_code' => $this->generateItemCode($finalName),
                'status' => ['active', 'active', 'active', 'inactive'][rand(0, 3)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $counter++;
            
            // Insert in chunks of 100 to avoid memory issues
            if (count($products) >= 100) {
                Product::insert($products);
                $products = [];
                $bar->advance(100);
            }
        }

        // Insert remaining products
        if (!empty($products)) {
            Product::insert($products);
            $bar->advance(count($products));
        }

        $bar->finish();
        $this->command->info("\n1000 products seeded successfully!");
    }

    private function generateDescription($name, $form)
    {
        $descriptions = [
            'tablet' => 'Oral tablet for systemic treatment.',
            'capsule' => 'Oral capsule for systemic treatment.',
            'injection' => 'Sterile injection for parenteral administration.',
            'cream' => 'Topical cream for external use.',
            'suspension' => 'Oral suspension for systemic treatment.',
            'ampoule' => 'Ampoule for parenteral administration.',
        ];
        
        $formType = strtolower($form);
        $desc = $descriptions[$formType] ?? 'Pharmaceutical product for medical use.';
        
        return $name . ' - ' . $desc;
    }

    private function getPackagingType($form)
    {
        $types = [
            'tablet' => 'pack',
            'capsule' => 'pack',
            'injection' => 'unit',
            'cream' => 'unit',
            'suspension' => 'unit',
            'ampoule' => 'unit',
        ];
        
        return $types[strtolower($form)] ?? 'pack';
    }

    private function getPackSize($form)
    {
        $sizes = [
            'tablet' => rand(5, 30),
            'capsule' => rand(5, 30),
            'injection' => 1,
            'cream' => 1,
            'suspension' => 1,
            'ampoule' => 1,
        ];
        
        return $sizes[strtolower($form)] ?? 10;
    }

    private function getUnit($form)
    {
        $units = [
            'tablet' => 'tablet(s)',
            'capsule' => 'capsule(s)',
            'injection' => 'injection(s)',
            'cream' => 'tube(s)',
            'suspension' => 'bottle(s)',
            'ampoule' => 'ampoule(s)',
        ];
        
        return $units[strtolower($form)] ?? 'unit(s)';
    }

    private function generateItemCode($name)
    {
        // Generate item code from product name
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $word) {
            if (preg_match('/\d/', $word)) {
                $code .= $word;
            } else {
                $code .= strtoupper(substr($word, 0, 2));
            }
        }
        return 'ITEM-' . $code . '-' . rand(100, 999);
    }
}