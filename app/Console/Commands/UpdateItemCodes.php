<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateItemCodes extends Command
{
    protected $signature = 'item:generate-codes';
    protected $description = 'Generate and update item codes';

    public function handle()
    {
        // Generate unique item codes
        // $items = DB::table('tbl_items')
        //     ->where('status', 'A')
        //     ->where('catID', 1)
        //     ->orderBy('itemcode', 'asc')
        //     ->get();

        $items  = DB::connection('pims')->select(
            "SELECT itemid, item, itemcode
                FROM tbl_items
                WHERE status = 'A'
                AND catID = 1
                ORDER BY itemcode ASC
                LIMIT 18446744073709551615;"
        );

        $updatedItems = [];
        foreach ($items as $item) {
            $newItemCode = 'MS-' . mt_rand(10000, 99999);
            // Ensure the new code is unique
            while (in_array($newItemCode, $updatedItems)) {
                $newItemCode = 'MS-' . mt_rand(10000, 99999);
            }
            $updatedItems[] = $newItemCode;

            // Update the item code in the database
            DB::connection('pims')->table('tbl_items')
                ->where('itemid', $item->itemid)
                ->update(['itemcode' => $newItemCode]);
        }

        $this->info('Item codes updated successfully.');
    }
}
