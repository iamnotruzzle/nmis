<?php

namespace App\Http\Controllers\Wards\ManualInventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WardManualInventoryController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {

        return Redirect::route('requeststocks.index');
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
