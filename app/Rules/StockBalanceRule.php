<?php

namespace App\Rules;

use App\Models\LocationStockBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockBalanceRule implements Rule
{
    public function __construct($params)
    {
        $this->cl2comb = $params;
    }

    public function passes($attribute, $value)
    {
        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
            FROM
                user_acc u
            INNER JOIN
                csrw_login_history l ON u.employeeid = l.employeeid
            WHERE
                l.employeeid = ?
            ORDER BY
                l.created_at DESC;
            ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;

        $date = Carbon::now()->subDays(30); // get last 30 days

        $stockBalCount = LocationStockBalance::where('cl2comb', $this->cl2comb)
            ->where('created_at', '>=', $date)
            ->where('location', $authCode)
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
