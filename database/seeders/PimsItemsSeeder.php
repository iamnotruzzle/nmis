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

        $duplicateItemsRemoved = [];
        $seenItems = [];

        foreach ($data as $item) {
            // Trim and convert the item property to lowercase before checking for duplicates
            $trimmedLowerItem = strtolower(trim($item->item));

            // Ensure you're accessing object properties correctly
            if (!isset($seenItems[$trimmedLowerItem]) && !in_array($trimmedLowerItem, array_column($duplicateItemsRemoved, 'item'))) {
                $duplicateItemsRemoved[] = $item;
                $seenItems[$trimmedLowerItem] = true;
            }
        }

        // dd($duplicateItemsRemoved);

        foreach ($duplicateItemsRemoved as $obj) {
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
