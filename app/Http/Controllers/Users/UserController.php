<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
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
            $query->where('firstName', 'LIKE', '%' . $value . '%')
                ->where('middleName', 'LIKE', '%' . $value . '%')
                ->where('lastName', 'LIKE', '%' . $value . '%');
            // ->orWhere('email', 'LIKE', '%' . $value . '%');
        })
            ->orderBy('lastName', 'asc')
            ->paginate(50);

        return Inertia::render('Users/Index', ['users' => $users]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $image = '';

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
            'firstName' => 'required|string',
            'middleName' => 'string|nullable',
            'lastName' => 'required|string',
            'suffix' => 'string|nullable',
            // 'role' => 'required|string',
            // 'permissions' => 'required',
            'email' => 'required|email|unique:users,email|max:40',
            'username' => 'required|string|unique:users,username|max:14',
            'password' => 'required|min:8',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('image', 'public');
        } else {
            $image = null;
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'middleName' => $request->middleName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'image' => $image,
        ]);

        dd($user);

        // assign role
        // $user->assignRole($request->role);

        // assign permissions
        // $user->givePermissionTo($request->permissions);

        // return redirect()->back();
        return Redirect::route('users.index');
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
