<?php

namespace App\Http\Controllers\Wards\WardStockAdjustment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WardsStocks;
use App\Models\WardStockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

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

    public function getWardStockHistory(Request $request)
    {
        try {
            $wardcode = $request->input('wardcode');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            // Base query for all stock adjustments in this ward
            $adjustmentsQuery = DB::table('csrw_ward_stock_adjustment as wsa')
                ->leftJoin('csrw_wards_stocks as ws', 'wsa.ward_stock_id', '=', 'ws.id')
                ->leftJoin('hclass2 as h2', 'wsa.cl2comb', '=', 'h2.cl2comb')
                ->select([
                    'wsa.id',
                    'wsa.created_at as adjustment_date',
                    'wsa.quantity_used',
                    'wsa.previous_quantity',
                    'wsa.new_quantity',
                    'wsa.remarks',
                    'wsa.tag',
                    'wsa.employeeid',
                    'wsa.cl2comb',
                    'h2.cl2desc as item_description'
                ])
                ->where('ws.location', $wardcode);

            // Add date filters based on adjustment created_at with proper time handling
            if ($dateFrom) {
                // Ensure we start from beginning of the day (00:00:00)
                $adjustmentsQuery->whereDate('wsa.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                // Ensure we include the entire end day (until 23:59:59)
                $adjustmentsQuery->whereDate('wsa.created_at', '<=', $dateTo);
            }

            $adjustments = $adjustmentsQuery
                ->orderBy('wsa.created_at', 'desc')
                ->get();

            // Debug logging (remove after testing)
            \Log::info('Date filters applied:', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'wardcode' => $wardcode,
                'results_count' => $adjustments->count()
            ]);

            // Get current stock summary for the ward
            $currentStockSummary = DB::table('csrw_wards_stocks as ws')
                ->leftJoin('hclass2 as h2', 'ws.cl2comb', '=', 'h2.cl2comb')
                ->select([
                    DB::raw('COUNT(DISTINCT ws.cl2comb) as total_items'),
                    DB::raw('SUM(ws.quantity) as total_quantity')
                ])
                ->where('ws.location', $wardcode)
                ->where('ws.quantity', '>', 0)
                ->first();

            // Calculate totals
            $totalAdjusted = $adjustments->sum('quantity_used');
            $totalAdjustmentRecords = $adjustments->count();
            $uniqueItemsAdjusted = $adjustments->unique('cl2comb')->count();

            // Format the response
            $wardStockHistory = [
                'ward_code' => $wardcode,
                'total_current_items' => $currentStockSummary->total_items ?? 0,
                'total_current_quantity' => $currentStockSummary->total_quantity ?? 0,
                'total_adjusted' => $totalAdjusted,
                'total_adjustment_records' => $totalAdjustmentRecords,
                'unique_items_adjusted' => $uniqueItemsAdjusted,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'adjustments' => $adjustments->map(function ($adjustment) {
                    return [
                        'id' => $adjustment->id,
                        'date' => Carbon::parse($adjustment->adjustment_date)->format('M d, Y g:i A'),
                        'item_description' => $adjustment->item_description,
                        'quantity_used' => $adjustment->quantity_used,
                        'previous_quantity' => $adjustment->previous_quantity,
                        'new_quantity' => $adjustment->new_quantity,
                        'remarks' => $adjustment->remarks,
                        'tag' => $adjustment->tag,
                        'employee_id' => $adjustment->employeeid,
                    ];
                })
            ];

            return response()->json($wardStockHistory);

        } catch (\Exception $e) {
            \Log::error('Ward stock history error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch ward stock history: ' . $e->getMessage()
            ], 500);
        }
    }
}
