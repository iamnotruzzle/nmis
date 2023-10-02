<?php

namespace App\Rules;

use App\Models\LocationStockBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockBalanceNotDeclaredYetRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->cl2comb = $params;

        // dd('__construct');
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
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $date = Carbon::now()->subDays(30); // get last 30 days


        $stockBalCount = LocationStockBalance::where('cl2comb', $this->cl2comb)
            ->where('created_at', '>=', $date)
            ->where('location', $authWardcode->wardcode)
            ->count();

        // dd($stockBalCount);

        if ($stockBalCount == 0) {
            return false; // false means the validation didn't pass then show validation error
        } else {
            return true;
        }
        // dd($stockBalCount);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The stock balance has not yet been declared.';
    }
}
