<?php

namespace App\Http\Controllers\Wards\Consignment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ConsignmentController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        dd('consignment');

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
