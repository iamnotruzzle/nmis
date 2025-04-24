<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\Location;
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

        // Fortify::authenticateUsing(function (Request $request) {
        //     // dd($request->password);

        //     // $user = User::where('employeeid', $request->login)->first();
        //     $user = User::where('user_name', $request->login)->first();
        //     // dd($user);

        //     if ($request->wardcode != null || $request->wardcode != '') {
        //         // 1st step: encrypt inputted password password
        //         $enc = DB::select("select dbo.ufn_crypto('" . $request->password . "', 1) as decrypted_pass");
        //         // dd($enc[0]->decrypted_pass);

        //         // 2nd step: decrypt the newly encrypted password
        //         $dec = DB::select("select dbo.ufn_crypto('" . $enc[0]->decrypted_pass . "', 0) as decrypted_pass");
        //         // dd($dec[0]->decrypted_pass);

        //         // 3rd step: decrypt the password again motherfucker, but directly from the table and where user_name is $request login
        //         $decrypted_pass_from_db = DB::select("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass_from_db FROM user_acc WHERE user_name = ?", [$request->login]);

        //         if ($user && $dec[0]->decrypted_pass == $decrypted_pass_from_db[0]->decrypted_pass_from_db) {
        //             // return $user;
        //             if ($request->wardcode == 'CSR' && $user->designation == 'csr') {
        //                 return $user;
        //             } elseif ($request->wardcode != 'CSR' && $request->wardcode != 'ADMIN' && $user->designation == 'ward') {
        //                 return $user;
        //             } elseif ($request->wardcode == 'ADMIN' && $user->designation == 'admin') {
        //                 return $user;
        //             } else {
        //                 throw ValidationException::withMessages(["You don't have permission to access this location."]);
        //             }
        //         }
        //     } else {
        //         throw ValidationException::withMessages(["The location field is required."]);
        //     }
        // });


        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('user_name', $request->login)->first();

            if ($request->wardcode != null || $request->wardcode != '') {
                $enc = DB::select("select dbo.ufn_crypto('" . $request->password . "', 1) as decrypted_pass");
                $dec = DB::select("select dbo.ufn_crypto('" . $enc[0]->decrypted_pass . "', 0) as decrypted_pass");
                $decrypted_pass_from_db = DB::select("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass_from_db FROM user_acc WHERE user_name = ?", [$request->login]);

                if ($user && $dec[0]->decrypted_pass == $decrypted_pass_from_db[0]->decrypted_pass_from_db) {

                    $designation = $user->designation;
                    $wardcode = $request->wardcode;

                    // Only log the login if designation matches the selected ward type
                    $allowed = false;
                    if ($wardcode == 'CSR' && $designation == 'csr') {
                        $allowed = true;
                    } elseif ($wardcode == 'ADMIN' && $designation == 'admin') {
                        $allowed = true;
                    } elseif ($wardcode != 'CSR' && $wardcode != 'ADMIN' && $designation == 'ward') {
                        $allowed = true;
                    }

                    if ($allowed) {
                        // ✅ Save login history
                        DB::table('csrw_login_history')->insert([
                            'employeeid' => $user->employeeid,
                            'wardcode' => $wardcode,
                            'created_at' => now()
                        ]);

                        // ✅ Optionally clear the cache so the new ward gets picked up immediately
                        Cache::forget('c_authWardCode_' . $user->employeeid);
                        Cache::forget('c_locationType_' . $user->employeeid);

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
