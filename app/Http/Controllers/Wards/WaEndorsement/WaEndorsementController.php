<?php

namespace App\Http\Controllers\Wards\WaEndorsement;

use App\Http\Controllers\Controller;
use App\Models\WaEndorsement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WaEndorsementController extends Controller
{
    public function index()
    {
        $endorsements = WaEndorsement::with(['details', 'form_user', 'to_user'])
            ->paginate(5);

        return Inertia::render('Wards/WaEndorsements/Index', [
            'endorsements' => $endorsements,
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
