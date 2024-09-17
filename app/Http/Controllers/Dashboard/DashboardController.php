<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // restrict controller based on the users role
    // public function __construct()
    // {
    //     // this will disable routes for users that is not super-admin or admin
    //     $this->middleware(['role:super-admin']);
    //     // $this->middleware(['role:super-admin|admin']);
    //     // $this->middleware(['role_or_permission:super-admin|admin|edit-users']);
    //     // $this->middleware(['permission:create-users']);
    // }

    public function index(Request $request)
    {
        return Inertia::render('Dashboard/Index', []);
    }
}
