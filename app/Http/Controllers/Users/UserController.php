<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{

    // restrict controller based on the users role
    // public function __construct()
    // {
    //     // this will disable routes for users that is not super-admin or admin
    //     $this->middleware(['role:super-admin|admin']);
    //     // $this->middleware(['role_or_permission:super-admin|admin|edit-users']);
    //     // $this->middleware(['permission:create-users']);
    // }

    public function index(Request $request)
    {
        $users = User::when($request->search, function ($query, $value) {
            $query->where('name', 'LIKE', '%' . $value . '%');
            // ->orWhere('email', 'LIKE', '%' . $value . '%');
        })
            ->paginate(20);

        return Inertia::render('Users/Index', ['users' => $users]);
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
