<?php

namespace App\Http\Controllers\Users\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $auth = Auth::user();
        // dd($auth->id);

        $image = $auth->image;

        if ($request->password != null || $request->password != '') {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
                'password' => 'required|min:8',
            ]);

            if ($request->hasFile('image')) {
                Storage::delete('public/' . $auth->image);
                $image = $request->file('image')->store('image', 'public');
            }

            User::where('id', $auth->id)
                ->update([
                    'password' => Hash::make($request->password),
                    'image' => $image,
                ]);
        } else {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5048',
            ]);

            if ($request->hasFile('image')) {
                Storage::delete('public/' . $auth->image);
                $image = $request->file('image')->store('image', 'public');
            }

            User::where('id', $auth->id)
                ->update([
                    'image' => $image,
                ]);
        }

        return redirect()->back();
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
