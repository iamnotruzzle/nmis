<?php

namespace App\Http\Controllers\Wards\TransferStock;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use Illuminate\Http\Request;

class TransferStockController extends Controller
{

    public function index(Request $request)
    {
        $employeeids = UserDetail::where('empstat', 'A')->get('employeeid');

        // $users = User::with(['roles', 'permissions', 'userDetail'])
        //     ->when($request->search, function ($query, $value) {
        //         $query->where('employeeid', 'LIKE', '%' . $value . '%');
        //     })
        //     ->when(
        //         $request->from,
        //         function ($query, $value) {
        //             $query->whereDate('created_at', '>=', $value);
        //         }
        //     )
        //     ->when(
        //         $request->to,
        //         function ($query, $value) {
        //             $query->whereDate('created_at', '<=', $value);
        //         }
        //     )
        //     ->orderBy('employeeid', 'asc')
        //     ->paginate(15);

        return Inertia::render('Wards/TransferStock/Index', [
            // 'users' => $users,
            'employeeids' => $employeeids
        ]);
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
