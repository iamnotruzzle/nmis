<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            // dd($request);

            $user = User::where('employeeid', $request->login)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if ($request->wardcode == 'CSR' && $user->designation == 'csr') {
                    return $user;
                } elseif ($request->wardcode != 'CSR' && $request->wardcode != 'ADMIN' && $user->designation == 'ward') {
                    return $user;
                } elseif ($request->wardcode == 'ADMIN' && $user->designation == 'admin') {
                    return $user;
                } else {
                    throw ValidationException::withMessages(["You don't have permission to access this location."]);
                    // return redirect()->back()->withErrors("You don't have permission to access this location.");
                }
            }
        });
    }

    /**
     * Configure the permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
