<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PimsItemsSeeder extends Seeder
{
    public function generateUniqueID($length = 5)
    {
        return Str::random($length);
    }

    public function run()
    {
        // $categories = item column is null, ' ' or '.', the item column will be equal to its description column
        $categories = File::get('database/data/pims_items_medical_supplies.json');
        $data = json_decode($categories);

        // normalized and duplicate items are remove
        $duplicatedItemsRemoved = [];
        $seenItems = [];

        // normalized and duplicate descriptions are remove
        $duplicatedDescriptionsRemoved = [];
        $seenDescriptions = [];


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
                'cl1stat' => 'A',
                'cl1lock' => 'N',
                'cl1upsw' => 'P',
                'cl1dtmd' => NULL,
                'compense' => NULL,
                'catID' => $obj->catid,
            ]);
        }

        $hclass1 = DB::select(
            "SELECT cl1comb, cl1desc
                FROM hclass1
                WHERE cl1comb LIKE '1000-%'
                ORDER BY cl1desc ASC",
        );

        // populating item table
        foreach ($hclass1 as $hclass) {
            // Normalize hclass1.cl1desc
            $normalizedHclassDesc = strtolower(trim(preg_replace('/[\s\p{P}]+$/u', '', $hclass->cl1desc)));

            foreach ($data as $obj) {
                // Normalize data.item
                $normalizedItem = strtolower(trim(preg_replace('/[\s\p{P}]+$/u', '', $obj->item)));

                // Calculate MD5 hash of normalized strings
                $hclassDescHash = md5($normalizedHclassDesc);
                $itemHash = md5($normalizedItem);

                // Compare MD5 hashes
                if ($hclassDescHash === $itemHash) {
                    // Create item if hclass1.cl1desc is EQUAL TO data.item
                    $uniqueID = $this->generateUniqueID();
                    $item = Item::create([
                        'cl2comb' => $hclass->cl1comb . '-' . $uniqueID,
                        'cl1comb' => $hclass->cl1comb,
                        'cl2code' => $uniqueID,
                        'stkno' => '',
                        'cl2desc' => $obj->description, // Assuming description is provided in $data
                        'cl2retprc' => 0.00,
                        'uomcode' => 'box',
                        'cl2dteas' => Carbon::now(),
                        'cl2stat' => $obj->status == 'C' ? 'I' : $obj->status,
                        'cl2lock' => 'N',
                        'cl2upsw' => 'P',
                        'cl2dtmd' => NULL,
                        'curcode' => NULL,
                        'cl2purp' => NULL,
                        'curcode1' => NULL,
                        'uomcode1' => NULL,
                        'cl2ctr' => NULL,
                        'stockbal' => 0.00,
                        'pharmaceutical' => NULL,
                        'baldteasof' => Carbon::now(),
                        'begbal' => 0.00,
                        'lot_no' => '',
                        'barcode' => NULL,
                        'rpoint' => NULL,
                        'catID' => $obj->catid
                    ]);
                }
            }
        }
    }
}
