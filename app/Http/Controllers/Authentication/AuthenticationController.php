<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthenticationController extends Controller
{
    public function index()
    {
        $locations = Location::where('wardstat', 'A')
            ->orderBy('wardname', 'ASC')
            ->get();

        return Inertia::render('Auth/Login', [
            'locations' => $locations,
        ]);
    }
}
