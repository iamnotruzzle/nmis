<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ItemsForCsrSuppliesSeeder extends Seeder
{
    public function run()
    {
        $items
            = [
                ['cl1code' => '90', 'cl2desc' => 'Acid Concentrate without Potassium 5 Liters Note: should be compatible with powdered mixed and with the existing machine Preparation: (Composition g/L): Glucose 0-49.5g; Sodium Chloride 216.19-270.8g', 'catid' => 9],
                ['cl1code' => '91', 'cl2desc' => 'Hemodialysis Bicarbonate (Powdered Mixed) Concentrated with sodium chloride Note: with FDA certificate; 1 pouch should be equal to 10 lites when mixed 896g. Should be compatible with the existing machine', 'catid' => 9],
                ['cl1code' => '93', 'cl2desc' => 'Povidone Iodine 10%, 60mL', 'catid' => 9],
                ['cl1code' => '100', 'cl2desc' => 'Acetazolamide 250mg tab', 'catid' => 9],
                ['cl1code' => '101', 'cl2desc' => 'Clarithromycin 250mg/5mL granules/powder for suspension, 50mL', 'catid' => 9],
                ['cl1code' => '104', 'cl2desc' => 'Aciclovir 200mg, tablet', 'catid' => 9],
                ['cl1code' => '105', 'cl2desc' => 'Aciclovir 25mg/mL, 10mL (IV infusion)', 'catid' => 9],
                ['cl1code' => '106', 'cl2desc' => 'Adenosine 3mg/mL, 2mL IV', 'catid' => 9],
                ['cl1code' => '108', 'cl2desc' => 'Albumin, Human 25%, 50mL IV, IV Infusion', 'catid' => 9],
                ['cl1code' => '112', 'cl2desc' => 'Allopurinol 100mg, tablet', 'catid' => 9],
                ['cl1code' => '113', 'cl2desc' => 'Allopurinol 300mg, tablet', 'catid' => 9],
                ['cl1code' => '114', 'cl2desc' => 'Alprazolam 250mcg, tablet', 'catid' => 9],
                ['cl1code' => '115', 'cl2desc' => 'Alprazolam 500mcg, tablet', 'catid' => 9],
                ['cl1code' => '117', 'cl2desc' => 'Amikacin 250mg/mL, 2mL (500mg) (IM, IV) (as sulfate)', 'catid' => 9],
                ['cl1code' => '119', 'cl2desc' => 'All-in-one Admixtures (also called 3 in 1" or "dual energy" solutions) 1000kcal in soybean oil based 1250mL Solution: Volume: 400-2500mL; Concentration: Variable; Protein: 3-6g/100mL; Carbohydrate: 6-15g/100mL; Lipid: 2-5g/100mL: Calories: variable; Electrolytes: Variable"', 'catid' => 9],
                ['cl1code' => '121', 'cl2desc' => 'Amiodarone 50mg/mL, 3mL (IV) (as hydrochloride)', 'catid' => 9],
                ['cl1code' => '667', 'cl2desc' => 'Compressed Air', 'catid' => 9],
                ['cl1code' => '669', 'cl2desc' => 'Oxygen Flask Type', 'catid' => 9],
                ['cl1code' => '670', 'cl2desc' => 'All-in-one Admixtures (also called 3 in 1" or "dual energy" solutions) 1400kcal in soybean oil based 1875 Solution: Volume: 400-2500mLL; Concentration: Variable; Protein: 3-6g/100mL; Carbohydrate: 6-15g/100mL; Lipiid: 2-5g/100mL; Calories: Variable; Electrolytes: Variable"', 'catid' => 9],
                ['cl1code' => '671', 'cl2desc' => 'Activated Charcoal powder, USP grade given as slurry 1kg', 'catid' => 9],
                ['cl1code' => '672', 'cl2desc' => 'Acetylcysteine 100mg', 'catid' => 9],
                ['cl1code' => '673', 'cl2desc' => 'Acetylcysteine 200mg', 'catid' => 9],
                ['cl1code' => '674', 'cl2desc' => 'Acetylcysteine 600mg effervescent', 'catid' => 9],
                ['cl1code' => '675', 'cl2desc' => 'Acetylcysteine 200mg/ml, 25 mL IV Infusion', 'catid' => 9],
                ['cl1code' => '676', 'cl2desc' => 'Albumin, Human 20%, 50mL (IV, IV Infusion)', 'catid' => 9],
                ['cl1code' => '679', 'cl2desc' => 'Alfuzosin 10mg (as hydrochloride)', 'catid' => 9],
                ['cl1code' => '680', 'cl2desc' => 'Hydrocortisone 50mg/mL, (as sodium succinate), 2mL (IM,IV)', 'catid' => 9],
                ['cl1code' => '681', 'cl2desc' => 'Hydrocortisone 125mg/mL, (as sodium succinate) 2mL (IM,IV)', 'catid' => 9],
                ['cl1code' => '684', 'cl2desc' => 'Aluminum Hydroxide 225mg + Magnesium hydroxide 200mg/5mL; 120ml suspension', 'catid' => 9],
            ];

        $itemData = [];

        foreach ($items as $item) {
            $csrSuppliesSubCategory = Category::where('ptcode', '1000')
                ->where('cl1code', 'p' . $item['cl1code'])
                ->first();
            // dd('p' . $item['cl1code']);

            if ($csrSuppliesSubCategory->cl1code == 'p' . $item['cl1code']) {
                array_push(
                    $itemData,
                    [
                        'cl2comb' => $csrSuppliesSubCategory['cl1comb'] . '-' . '101',
                        'cl1comb' => $csrSuppliesSubCategory['cl1comb'],
                        'cl2code' => '101',
                        'stkno' => '',
                        'cl2desc' => $item['cl2desc'],
                        'cl2retprc' => 0.00,
                        'uomcode' => 'PC',
                        'cl2dteas' => Carbon::now(),
                        'cl2stat' => 'A',
                        'cl2lock' => 'N',
                        'cl2upsw' => 'P',
                        'cl2dtmd' => NULL,
                        'curcode' => NULL,
                        'cl2purp' => NULL,
                        'curcode1' => NULL,
                        'uomcode1' => NULL,
                        'cl2ctr' => NULL,
                        'brandname' => NULL,
                        'stockbal' => 0.00,
                        'pharmaceutical' => NULL,
                        'pharmaceutical' => NULL,
                        'baldteasof' => Carbon::now(),
                        'begbal' => 0.00,
                        'lot_no' => '',
                        'barcode' => NULL,
                        'rpoint' => NULL,
                        'catID' => $item['catid']
                    ]
                );
            } else {
                return null;
            }
        }
        // dd($itemData);
        Item::insert($itemData);
    }
}
