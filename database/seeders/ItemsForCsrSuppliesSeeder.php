<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemsForCsrSuppliesSeeder extends Seeder
{
    public function generateUniqueID($length = 10)
    {
        return Str::random($length);
    }

    public function run()
    {
        $json = File::get('database/data/pims_items.json');
        $data = json_decode($json);

        try {
            foreach ($data as $obj) {

                $uniqueID = $this->generateUniqueID();

                $item = Item::firstOrCreate(
                    [
                        // 1000 = ptcode from hclass1
                        // 'p' . $obj->cl1code = cl1code from hclass1
                        'cl2comb' => '1000' . '-' . 'p' . $obj->cl1code . '-' . $uniqueID,
                        'cl1comb' => '1000' . '-' . 'p' . $obj->cl1code,
                        'cl2code' => $uniqueID,
                        'stkno' => '',
                        'cl2desc' => $obj->cl2desc,
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
                        'catID' => $obj->catID
                    ]
                );
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Seeder failed: ' . $e->getMessage());

            // You can also log additional information like the stack trace
            Log::error($e->getTraceAsString());

            // Optionally, rethrow the exception to stop the seeder execution
            throw $e;
        }
    }
}
