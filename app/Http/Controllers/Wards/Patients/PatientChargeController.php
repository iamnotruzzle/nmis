<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientChargeController extends Controller
{
    public function index(Request $request)
    {
        $enccode = $request->enccode;
        dd($enccode);
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
