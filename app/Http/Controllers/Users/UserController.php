<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{

    // restrict controller based on the users role
    public function __construct()
    {
        // this will disable routes for users that is not super-admin or admin
        $this->middleware(['role:super-admin']);
        // $this->middleware(['role:super-admin|admin']);
        // $this->middleware(['role_or_permission:super-admin|admin|edit-users']);
        // $this->middleware(['permission:create-users']);
    }

    public function index(Request $request)
    {
        // dd(Auth::user());

        $employeeids = UserDetail::where('empstat', 'A')->get('employeeid');

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        $users = User::with(['roles', 'permissions', 'userDetail'])
            ->when($request->search, function ($query, $value) {
                $query->where('employeeid', 'LIKE', '%' . $value . '%');
            })
            ->when(
                $request->from,
                function ($query, $value) use ($from) {
                    $query->whereDate('created_at', '>=', $from);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) use ($to) {
                    $query->whereDate('created_at', '<=', $to);
                }
            )
            ->orderBy('employeeid', 'asc')
            ->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'employeeids' => $employeeids
        ]);
    }

    public function store(Request $request)
    {
        $image = '';

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
            'role' => 'required',
            // 'permissions' => 'required',
            'employeeid' => 'required|string|unique:csrw_users,employeeid|max:14',
            'password' => 'required|min:8',
            'designation' => 'required',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('image', 'public');
        } else {
            $image = null;
        }

        $user = User::create([
            'employeeid' => $request->employeeid,
            'password' => bcrypt($request->password),
            'image' => $image,
            'designation' => $request->designation,
        ]);

        // dd($user);

        // assign role
        $user->assignRole($request->role);

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
                'role' => 'required',
                // 'permissions' => 'required',
                'employeeid' => [
                    'required',
                    'string',
                    'max:14',
                    Rule::unique('csrw_users')->ignore($user->id)
                ],
                'password' => 'required|min:8',
                'designation' => 'required',
            ]);

            if ($request->hasFile('image')) {
                Storage::delete('public/' . $user->image);
                $image = $request->file('image')->store('image', 'public');
            }

            $user->update([
                'employeeid' => $request->employeeid,
                'password' => bcrypt($request->password),
                'image' => $image,
                'designation' => $request->designation,
            ]);
        } else {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
                'role' => 'required',
                // 'permissions' => 'required',
                'employeeid' => [
                    'required',
                    'string',
                    'max:14',
                    Rule::unique('csrw_users')->ignore($user->id)
                ],
                'designation' => 'required',
            ]);

            if ($request->hasFile('image')) {
                Storage::delete('public/' . $user->image);
                $image = $request->file('image')->store('image', 'public');
            }

            $user->update([
                'employeeid' => $request->employeeid,
                'image' => $image,
                'designation' => $request->designation,
            ]);
        }

        // update user role
        $user->syncRoles($request->role);

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
