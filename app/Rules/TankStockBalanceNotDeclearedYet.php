<?php

namespace App\Rules;

use App\Models\LocationTankStockBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TankStockBalanceNotDeclearedYet implements Rule
{
    public function __construct($params)
    {
        $this->itemcode = $params;
        $this->noBalance = array();
    }

    public function passes($attribute, $value)
    {
        // dd('passes');
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

        // if ($stockBalCount == 0) {
        //     $stockBalDesc = Item::where('itemcode', $this->itemcode)
        //         ->first();

        //     $this->noBalance = trim($stockBalDesc['cl2desc']);
        // }

        if ($stockBalCount == 0) {
            return false; // false means the validation didn't pass then show validation error
        } else {
            return true;
        }
    }

    public function message()
    {
        return $this->itemcode;
    }
}
