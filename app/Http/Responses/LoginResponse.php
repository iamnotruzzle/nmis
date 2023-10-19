<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{

    public function toResponse($request)
    {
        $home = (Auth::user()->designation == 'admin') ? 'admindashboard' : ((Auth::user()->designation == 'csr') ? 'csrdashboard' : 'wardspatients');

        return redirect()->to($home);
    }
}
