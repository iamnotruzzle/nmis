<?php

namespace App\Http\Controllers\Wards\WardStockAdjustment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WardsStocks;
use App\Models\WardStockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class WardStockAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get current stock
            $currentStock = WardsStocks::find($request->id);

            if (!$currentStock) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Stock item not found']);
            }

            // Calculate new quantity (current - used)
            $quantityUsed = (int) $request->quantity;
            $newQuantity = $currentStock->quantity - $quantityUsed;

            // Prevent negative stock
            if ($newQuantity < 0) {
                DB::rollBack();
                return back()->withErrors([
                    'quantity' => 'Cannot use more quantity than available in stock. Available: ' . $currentStock->quantity
                ]);
            }

            // Update the ward stock with new quantity
            WardsStocks::where('id', $request->id)->update([
                'quantity' => $newQuantity,
            ]);

            // Log the adjustment with the quantity used (not the resulting quantity)
            WardStockAdjustment::create([
                'ward_stock_id' => $request->id,
                'cl2comb' => $request->cl2comb,
                'quantity_used' => $quantityUsed,
                'previous_quantity' => $currentStock->quantity,
                'new_quantity' => $newQuantity,
                'remarks' => trim($request->remarks),
                'tag' => $request->tag,
                'wardcode' => $request->wardcode,
                'employeeid' => $request->employeeid,
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process stock adjustment: ' . $e->getMessage()]);
        }

        return Redirect::route('wardinv.index')->with('success', 'Stock usage recorded successfully');
    }
}
