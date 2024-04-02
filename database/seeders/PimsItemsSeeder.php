<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PimsItemsSeeder extends Seeder
{
    public function generateUniqueID($length = 7)
    {
        return Str::random($length);
    }

    public function run()
    {
        // $categories = item column is null, ' ' or '.', the item column will be equal to its description column
        $categories = File::get('database/data/pims_items.categories');
        $data = json_decode($categories);

        // normalized and duplicate items are remove
        $duplicatedItemsRemoved = [];
        $seenItems = [];

        foreach ($data as $item) {
            // Normalize the item property by removing extra whitespace characters, trailing punctuation, converting to lowercase, and trimming
            $normalizedItem = strtolower(trim(preg_replace('/[\s\p{P}]+$/u', '', $item->item)));

            // Calculate MD5 hash of the normalized item
            $hash = md5($normalizedItem);

            // Check if the hash is already seen
            if (!isset($seenItems[$hash])) {
                // If not a duplicate, add to duplicatedItemsRemoved and seenItems
                $duplicatedItemsRemoved[] = $item;
                $seenItems[$hash] = true;
            }
        }

        // saving category to table
        foreach ($duplicatedItemsRemoved as $obj) {
            $trimmedItem = trim($obj->item);

            $category = Category::firstOrCreate([
                'cl1comb' => '1000' . '-' . $obj->itemid,
                'ptcode' => '1000',
                'cl1code' => $obj->itemid,
                'cl1desc' => $trimmedItem == null ? '.' : $trimmedItem,
                'cl1stat' => 'I',
                'cl1lock' => 'N',
                'cl1upsw' => 'P',
                'cl1dtmd' => NULL,
                'compense' => NULL,
                'catID' => $obj->catid,
            ]);
        }

        // saving items to table
        // foreach ($data as $obj) {
        //     $uniqueID = $this->generateUniqueID();

        //     $item = Item::firstOrCreate(
        //         [
        //             // 1000 = ptcode from hclass1
        //             // 'p' . $obj->cl1code = cl1code from hclass1
        //             'cl2comb' => '1000' . '-' . cl1code from category . '-' . $uniqueID,
        //             'cl1comb' => '1000' . '-' . cl1code from category,
        //             'cl2code' => $uniqueID,
        //             'stkno' => '',
        //             'cl2desc' => $obj->description,
        //             'cl2retprc' => 0.00,
        //             'uomcode' => 'PC',
        //             'cl2dteas' => Carbon::now(),
        //             'cl2stat' => 'I',
        //             'cl2lock' => 'N',
        //             'cl2upsw' => 'P',
        //             'cl2dtmd' => NULL,
        //             'curcode' => NULL,
        //             'cl2purp' => NULL,
        //             'curcode1' => NULL,
        //             'uomcode1' => NULL,
        //             'cl2ctr' => NULL,
        //             'brandname' => NULL,
        //             'stockbal' => 0.00,
        //             'pharmaceutical' => NULL,
        //             'pharmaceutical' => NULL,
        //             'baldteasof' => Carbon::now(),
        //             'begbal' => 0.00,
        //             'lot_no' => '',
        //             'barcode' => NULL,
        //             'rpoint' => NULL,
        //             'catID' => $obj->catID
        //         ]
        //     );
        // }
    }
}
