<?php

namespace App\Rules;

use App\Models\Item;
use App\Models\LocationStockBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockBalanceNotDeclaredYetRule implements Rule
{
    public function __construct($params)
    {
        $this->itemcode = $params;
        $this->noBalance = array();
    }

    public function passes($attribute, $value)
    {
        // dd('passes');
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

    }

    public function message()
    {
        return $this->itemcode;
    }
}
