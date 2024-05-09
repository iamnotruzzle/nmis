<?php

namespace Database\Seeders;

use App\Models\PimsSupplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SuppliersListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = File::get('database/data/tbl_suppliers_list.json');
        $data = json_decode($suppliers);

        foreach ($data as $obj) {
            $trimmedItem = trim($obj->suppname);

            $supplier = PimsSupplier::firstOrCreate([
                'supplierID' => $obj->supplierID,
                'suppname' => $trimmedItem,
                'status' => $obj->status,
            ]);
        }
    }
}
