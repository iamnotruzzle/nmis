<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
                ->orWhere('middleName', 'LIKE', '%' . $value . '%')
                ->orWhere('lastName', 'LIKE', '%' . $value . '%');
        })
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }
            )
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
            'email' => 'required|email:rfc,dns|unique:users,email|max:40',
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
            'suffix' => $request->suffix,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'image' => $image,
        ]);

        // dd($user);

        // assign role
        // $user->assignRole($request->role);

        // assign permissions
        // $user->givePermissionTo($request->permissions);

        // return redirect()->back();
        return Redirect::route('users.index');
    }

    public function update(User $user, Request $request)
    {
        // dd($request);

        $image = $user->image;

        if ($request->password != null || $request->password != '') {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
                'firstName' => 'required|string',
                'middleName' => 'string|nullable',
                'lastName' => 'required|string',
                'suffix' => 'string|nullable',
                // 'role' => 'required|string',
                // 'permissions' => 'required',
                'email' => [
                    'required',
                    // 'email',
                    'email:rfc,dns',
                    Rule::unique('users')->ignore($user->id)
                ],
                'username' => [
                    'required',
                    'string',
                    'max:14',
                    Rule::unique('users')->ignore($user->id)
                ],
                'password' => 'required|min:8'
            ]);

            if ($request->hasFile('image')) {
                Storage::delete('public/' . $user->image);
                $image = $request->file('image')->store('image', 'public');
            }

            $user->update([
                'firstName' => $request->firstName,
                'middleName' => $request->middleName,
                'lastName' => $request->lastName,
                'suffix' => $request->suffix,
                'email' => $request->email,
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'image' => $image
            ]);
        } else {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
                'firstName' => 'required|string',
                'middleName' => 'string|nullable',
                'lastName' => 'required|string',
                'suffix' => 'string|nullable',
                // 'role' => 'required|string',
                // 'permissions' => 'required',
                'email' => [
                    'required',
                    // 'email',
                    'email:rfc,dns',
                    Rule::unique('users')->ignore($user->id)
                ],
                'username' => [
                    'required',
                    'string',
                    'max:14',
                    Rule::unique('users')->ignore($user->id)
                ],
            ]);

            if ($request->hasFile('image')) {
                Storage::delete('public/' . $user->image);
                $image = $request->file('image')->store('image', 'public');
            }

            $user->update([
                'firstName' => $request->firstName,
                'middleName' => $request->middleName,
                'lastName' => $request->lastName,
                'suffix' => $request->suffix,
                'email' => $request->email,
                'username' => $request->username,
                'image' => $image
            ]);
        }

        // update user role
        // $user->syncRoles($request->role);

        // update user permissions
        // $user->syncPermissions([$request->permissions]);

        // return redirect()->back();
        return Redirect::route('users.index');
    }

    public function destroy(User $user)
    {
        Storage::delete('public/' . $user->image);

        $user->delete();

        // remove user role
        // $user->roles()->detach();

        // remove user role
        // $user->permissions()->detach();

        // return redirect()->back();
        return Redirect::route('users.index');
    }
}
