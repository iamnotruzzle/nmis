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
                ['catID' => 4, 'categoryname' => 'Housekeeping Supplies', 'status' => 'A'],
                ['catID' => 5, 'categoryname' => 'Electrical Supplies', 'status' => 'A'],
                ['catID' => 6, 'categoryname' => 'Laboratory Supplies & Reagents', 'status' => 'A'],
                ['catID' => 7, 'categoryname' => 'Building Maintenance Supplies', 'status' => 'A'],
                ['catID' => 8, 'categoryname' => 'IT Equipment', 'status' => 'A'],
                ['catID' => 9, 'categoryname' => 'Drugs and Medicines', 'status' => 'A'],
                ['catID' => 10, 'categoryname' => 'Dental Supplies', 'status' => 'A'],
                ['catID' => 11, 'categoryname' => 'Medical Equipment', 'status' => 'A'],
                ['catID' => 12, 'categoryname' => 'PT Rehabilitation', 'status' => 'A'],
                ['catID' => 13, 'categoryname' => 'Hemodialysis Supplies', 'status' => 'A'],
                ['catID' => 14, 'categoryname' => 'Radiology Supplies', 'status' => 'A'],
                ['catID' => 15, 'categoryname' => 'Electrical Equipment', 'status' => 'A'],
                ['catID' => 16, 'categoryname' => 'Medical Instruments', 'status' => 'A'],
                ['catID' => 17, 'categoryname' => 'Transportation Equipment', 'status' => 'A'],
                ['catID' => 18, 'categoryname' => 'Foods (Dietary) Supplies', 'status' => 'A'],
                ['catID' => 19, 'categoryname' => 'Laboratory Equipment', 'status' => 'A'],
                ['catID' => 20, 'categoryname' => 'Laboratory Instrument', 'status' => 'A'],
                ['catID' => 21, 'categoryname' => 'Office Equipment', 'status' => 'A'],
                ['catID' => 22, 'categoryname' => 'Accountable Forms', 'status' => 'A'],
                ['catID' => 23, 'categoryname' => 'IT License, Plan, Subscription', 'status' => 'A'],
                ['catID' => 24, 'categoryname' => 'Motorpool', 'status' => 'A'],
                ['catID' => 25, 'categoryname' => 'Non-accountable Forms', 'status' => 'A'],
                ['catID' => 26, 'categoryname' => 'Furnitures, Fixtures, Books', 'status' => 'A'],
                ['catID' => 27, 'categoryname' => 'Spare parts', 'status' => 'A'],
                ['catID' => 28, 'categoryname' => 'Other Supplies and Materials', 'status' => 'A'],
                ['catID' => 29, 'categoryname' => 'Testing Materials', 'status' => 'A'],
                ['catID' => 30, 'categoryname' => 'Consumer', 'status' => 'A'],
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
