<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;

class MedicalSuppliesSubCatSeeder extends Seeder
{
    public function run()
    {

        $json = File::get('database/data/medical_supplies_sub_category.json');
        $data = json_decode($json);

        foreach ($data as $obj) {
            $subCategory = Category::firstOrCreate([
                'cl1comb' => '1000' . '-' . 'p' . $obj->itemid,
                'ptcode' => '1000',
                'cl1code' => 'p' . $obj->itemid,
                'cl1desc' => $obj->item == null ? '.' : $obj->item,
                'cl1stat' => 'A',
                'cl1lock' => 'N',
                'cl1upsw' => 'P',
                'cl1dtmd' => NULL,
                'compense' => NULL,
                'catID' => $obj->catID,
            ]);
        }
    }
}
