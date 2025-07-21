<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\Location;
use App\Models\LoginHistory;
use App\Models\Sessions;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Str;

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
            // dd($request->password);

            // $user = User::where('employeeid', $request->login)->first();
            $user = User::where('user_name', $request->login)->first();
            // dd($user);

            if ($request->wardcode != null || $request->wardcode != '') {
                // 1st step: encrypt inputted password password
                $enc = DB::select("select dbo.ufn_crypto('" . $request->password . "', 1) as decrypted_pass");
                // dd($enc[0]->decrypted_pass);

                // 2nd step: decrypt the newly encrypted password
                $dec = DB::select("select dbo.ufn_crypto('" . $enc[0]->decrypted_pass . "', 0) as decrypted_pass");
                // dd($dec[0]->decrypted_pass);

                // 3rd step: decrypt the password again motherfucker, but directly from the table and where user_name is $request login
                $decrypted_pass_from_db = DB::select("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass_from_db FROM user_acc WHERE user_name = ?", [$request->login]);

                if ($user && $dec[0]->decrypted_pass == $decrypted_pass_from_db[0]->decrypted_pass_from_db) {
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


        // Fortify::authenticateUsing(function (Request $request) {
        //     $user = User::where('user_name', $request->login)->first();

        //     if ($request->wardcode != null || $request->wardcode != '') {
        //         $enc = DB::select("select dbo.ufn_crypto('" . $request->password . "', 1) as decrypted_pass");
        //         $dec = DB::select("select dbo.ufn_crypto('" . $enc[0]->decrypted_pass . "', 0) as decrypted_pass");
        //         $decrypted_pass_from_db = DB::select("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass_from_db FROM user_acc WHERE user_name = ?", [$request->login]);

        //         if ($user && $dec[0]->decrypted_pass == $decrypted_pass_from_db[0]->decrypted_pass_from_db) {
        //             //create login history
        //             LoginHistory::create([
        //                 'employeeid' => $user->employeeid,
        //                 'wardcode' => $request->wardcode
        //             ]);

        //             return $user;
        //         }
        //     } else {
        //         throw ValidationException::withMessages(["The location field is required."]);
        //     }
        // });


        // Fortify::authenticateUsing(function (Request $request) {
        //     $user = User::where('user_name', $request->login)->first();

        //     if (!$request->wardcode || trim($request->wardcode) === '') {
        //         throw ValidationException::withMessages(["wardcode" => "The location field is required."]);
        //     }

        //     if ($user) {
        //         $enc = DB::select("select dbo.ufn_crypto(?, 1) as decrypted_pass", [$request->password]);
        //         $dec = DB::select("select dbo.ufn_crypto(?, 0) as decrypted_pass", [$enc[0]->decrypted_pass]);
        //         $decrypted_pass_from_db = DB::select("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass_from_db FROM user_acc WHERE user_name = ?", [$request->login]);

        //         if ($dec[0]->decrypted_pass === $decrypted_pass_from_db[0]->decrypted_pass_from_db) {

        //             // ✅ Save pending login token and wardcode for next request
        //             session([
        //                 'pending_login_token' => (string) Str::uuid(),
        //                 'pending_wardcode' => $request->wardcode,
        //             ]);

        //             return $user;
        //         }
        //     }

        //     throw ValidationException::withMessages([
        //         Fortify::username() => __('auth.failed'),
        //     ]);
        // });
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
