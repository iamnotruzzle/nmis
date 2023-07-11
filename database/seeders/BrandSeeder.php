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
        $superAdminUser = Brand::factory()->create([

            // 'suffix' => null,
            'name' => 'NO BRAND',
            'status' => 'A',
        ]);
    }
}
