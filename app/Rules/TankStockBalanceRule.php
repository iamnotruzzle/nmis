<?php

namespace App\Rules;

use App\Models\LocationTankStockBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TankStockBalanceRule implements Rule
{
    public function __construct($params)
    {
        $this->itemcode = $params;
    }

    public function passes($attribute, $value)
    {
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $date = Carbon::now()->subDays(30); // get last 30 days

        $stockBalCount = LocationTankStockBalance::where('itemcode', $this->itemcode)
            ->where('created_at', '>=', $date)
            ->where('location', $authWardcode->wardcode)
            ->count();

        if ($stockBalCount != 0) {
            return false; // false means the validation didn't pass then show validation error
        } else {
            return true;
        }
        // dd($stockBalCount);
    }

    public function message()
    {
        return 'Stock balance of this item is already recorded.';
    }
}
