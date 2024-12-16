<?php

namespace App\Rules;

use App\Models\Item;
use App\Models\LocationStockBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CsrStockBalanceNotDeclaredYetRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->cl2comb = $params;
        $this->noBalance = array();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
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


        $stockBalCount = LocationStockBalance::where('cl2comb', $this->cl2comb)
            ->where('created_at', '>=', $date)
            ->where('location', $authCode)
            ->count();

        if ($stockBalCount == 0) {
            $stockBalDesc = Item::where('cl2comb', $this->cl2comb)
                ->first();

            $this->noBalance = trim($stockBalDesc['cl2desc']);
        }

        if ($stockBalCount == 0) {
            return false; // false means the validation didn't pass then show validation error
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '' . $this->noBalance . ' stock balance has not yet been declared.';
    }
}
