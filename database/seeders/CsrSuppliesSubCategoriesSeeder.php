<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CsrSuppliesSubCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories =
            [
                ['cl1code' => 90, 'cl1desc' => 'Acid Concentrate without Potassium'],
                ['cl1code' => 91, 'cl1desc' => 'Hemodialysis Bicarbonate (Powdered Mixed) Concentrated with sodium chloride'],
                ['cl1code' => 93, 'cl1desc' => 'POVIDONE IODINE'],
                ['cl1code' => 100, 'cl1desc' => 'tablet'],
                ['cl1code' => 101, 'cl1desc' => 'Clarithromycin'],
                ['cl1code' => 104, 'cl1desc' => 'TABLET'],
                ['cl1code' => 105, 'cl1desc' => 'vial'],
                ['cl1code' => 106, 'cl1desc' => 'ADENOSINE'],
                ['cl1code' => 108, 'cl1desc' => 'vial'],
                ['cl1code' => 112, 'cl1desc' => 'Allopurinol'],
                ['cl1code' => 113, 'cl1desc' => 'Allopurinol'],
                ['cl1code' => 114, 'cl1desc' => 'Alprazolam'],
                ['cl1code' => 115, 'cl1desc' => 'Alprazolam'],
                ['cl1code' => 117, 'cl1desc' => 'Amikacin'],
                ['cl1code' => 119, 'cl1desc' => 'All-in-one Admixture'],
                ['cl1code' => 121, 'cl1desc' => 'Amiodarone'],
                ['cl1code' => 667, 'cl1desc' => 'Compressed Air'],
                ['cl1code' => 669, 'cl1desc' => 'Oxygen'],
                ['cl1code' => 670, 'cl1desc' => 'All-in-one Admixture'],
                ['cl1code' => 671, 'cl1desc' => 'Activated Charcoal powder'],
                ['cl1code' => 672, 'cl1desc' => 'Acetylcysteine'],
                ['cl1code' => 673, 'cl1desc' => 'Acetylcysteine'],
                ['cl1code' => 674, 'cl1desc' => 'tablet'],
                ['cl1code' => 675, 'cl1desc' => 'Acetylcysteine'],
                ['cl1code' => 676, 'cl1desc' => 'Albumin'],
                ['cl1code' => 679, 'cl1desc' => 'Alfuzosin'],
                ['cl1code' => 680, 'cl1desc' => 'Hydrocortisone'],
                ['cl1code' => 681, 'cl1desc' => 'Hydrocortisone'],
                ['cl1code' => 684, 'cl1desc' => 'Aluminum Hydroxide'],
            ];

        $categoryData = [];

        foreach ($categories as $category) {
            array_push(
                $categoryData,
                [
                    'cl1comb' => '1000' . '-' . 'p' . $category['cl1code'],
                    'ptcode' => '1000',
                    'cl1code' => 'p' . $category['cl1code'],
                    'cl1desc' => $category['cl1desc'],
                    'cl1stat' => 'A',
                    'cl1lock' => 'N',
                    'cl1upsw' => 'P',
                    'cl1dtmd' => NULL,
                    'compense' => NULL,
                ]
            );

            // Category::create($categoryData);
        }

        Category::insert($categoryData);
    }
}
