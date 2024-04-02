<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;

class PimsItemsSeeder extends Seeder
{
    public function run()
    {
        // $json = item column is null, ' ' or '.', the item column will be equal to its description column
        $json = File::get('database/data/pims_items.json');
        $data = json_decode($json);

        // normalized and duplicate items are remove
        $newArray = [];
        $seenItems = [];

        foreach ($data as $item) {
            // Normalize the item property by removing extra whitespace characters, trailing punctuation, converting to lowercase, and trimming
            $normalizedItem = strtolower(trim(preg_replace('/[\s\p{P}]+$/u', '', $item->item)));

            // Calculate MD5 hash of the normalized item
            $hash = md5($normalizedItem);

            // Check if the hash is already seen
            if (!isset($seenItems[$hash])) {
                // If not a duplicate, add to newArray and seenItems
                $newArray[] = $item;
                $seenItems[$hash] = true;
            }
        }
        // dd($duplicateItemsRemoved);

        foreach ($newArray as $obj) {
            $trimmedItem = trim($obj->item);

            $subCategory = Category::firstOrCreate([
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
    }
}
