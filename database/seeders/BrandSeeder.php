<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create default brand
        Brand::create([

            // 'suffix' => null,
            'name' => 'NO BRAND',
            'status' => 'A',
        ]);
    }
}
