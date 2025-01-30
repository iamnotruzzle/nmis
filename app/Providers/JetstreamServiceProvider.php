<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\Sessions;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );

        Fortify::authenticateUsing(function (Request $request) {
            // dd($request);

            // $user = User::where('employeeid', $request->login)->first();
            $user = User::where('user_name', $request->login)->first();
            // dd($user);

            if ($request->wardcode != null || $request->wardcode != '') {
                // 1st step: decrypt password
                $decrypted_pass = DB::select("select dbo.ufn_crypto('" . $user->user_pass . "', 0) as decrypted_pass");
                // dd($decrypted_pass[0]->decrypted_pass);

                // 2nd step: decrypt the password again motherfucker, but directly from the table and where user_name is $request login
                $decrypted_pass_from_db = DB::select("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass_from_db FROM user_acc WHERE user_name = ?", [$request->login]);
                // dd($decrypted_pass_from_db[0]->decrypted_pass_from_db);

                // old condition
                // if ($user && Hash::check($request->password, $user->password)) {
                // new condition
                if ($user && $decrypted_pass[0]->decrypted_pass == $decrypted_pass_from_db[0]->decrypted_pass_from_db) {
                    // return $user;
                    if ($request->wardcode == 'CSR' && $user->designation == 'csr') {
                        return $user;
                    } elseif ($request->wardcode != 'CSR' && $request->wardcode != 'ADMIN' && $user->designation == 'ward') {
                        return $user;
                    } elseif ($request->wardcode == 'ADMIN' && $user->designation == 'admin') {
                        return $user;
                    } else {
                        throw ValidationException::withMessages(["You don't have permission to access this location."]);
                    }
                }
            } else {
                throw ValidationException::withMessages(["The location field is required."]);
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
