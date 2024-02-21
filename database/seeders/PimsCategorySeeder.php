<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PimsCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PimsCategorySeeder extends Seeder
{
    public function run()
    {
        $categories =
            [
                ['catID' => 1, 'categoryname' => 'Medical Supplies', 'status' => 'A'],
                ['catID' => 2, 'categoryname' => 'Office Supplies', 'status' => 'A'],
                ['catID' => 3, 'categoryname' => 'IT Supplies', 'status' => 'A'],
                ['catID' => 9, 'categoryname' => 'Drugs and Medicines', 'status' => 'A'],
            ];

        $categoryData = [];

        foreach ($categories as $category) {
            array_push(
                $categoryData,
                [
                    'catID' => $category['catID'],
                    'categoryname' => $category['categoryname'],
                    'status' => $category['status'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            // Category::create($categoryData);
        }

        PimsCategory::insert($categoryData);
    }
}
