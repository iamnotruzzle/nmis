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
        //             // Check wardcode and designation permissions
        //             if ($request->wardcode == 'CSR' && $user->designation == 'csr') {
        //                 // ✅ Save pending login token and wardcode for next request
        //                 session([
        //                     'pending_login_token' => (string) Str::uuid(),
        //                     'pending_wardcode' => $request->wardcode,
        //                 ]);
        //                 return $user;
        //             } elseif ($request->wardcode != 'CSR' && $request->wardcode != 'ADMIN' && $user->designation == 'ward') {
        //                 session([
        //                     'pending_login_token' => (string) Str::uuid(),
        //                     'pending_wardcode' => $request->wardcode,
        //                 ]);
        //                 return $user;
        //             } elseif ($request->wardcode == 'ADMIN' && $user->designation == 'admin') {
        //                 session([
        //                     'pending_login_token' => (string) Str::uuid(),
        //                     'pending_wardcode' => $request->wardcode,
        //                 ]);
        //                 return $user;
        //             } else {
        //                 throw ValidationException::withMessages(["You don't have permission to access this location."]);
        //             }
        //         }
        //     }

        //     throw ValidationException::withMessages([
        //         Fortify::username() => __('auth.failed'),
        //     ]);
        // });

        Fortify::authenticateUsing(function (Request $request) {
            // 1. Validate required fields
            if (!$request->wardcode || trim($request->wardcode) === '') {
                throw ValidationException::withMessages(["wardcode" => "The location field is required."]);
            }

            // 2. Fetch user account
            $user = User::where('user_name', $request->login)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    Fortify::username() => 'User not found',
                ]);
            }

            // 3. Encrypt the input password using your SQL Server function
            $encryptedInput = DB::selectOne("SELECT dbo.ufn_crypto(?, 1) AS encrypted_pass", [$request->password]);
            if (!$encryptedInput || !$encryptedInput->encrypted_pass) {
                throw ValidationException::withMessages([
                    Fortify::username() => 'Authentication failed',
                ]);
            }

            // 4. Decrypt the encrypted input password (round-trip decryption)
            $decryptedInput = DB::selectOne("SELECT dbo.ufn_crypto(?, 0) AS decrypted_pass", [$encryptedInput->encrypted_pass]);
            if (!$decryptedInput || !$decryptedInput->decrypted_pass) {
                throw ValidationException::withMessages([
                    Fortify::username() => 'Authentication failed',
                ]);
            }

            // 5. Decrypt the stored password from the DB
            $storedDecrypted = DB::selectOne("SELECT dbo.ufn_crypto(user_pass, 0) AS decrypted_pass FROM user_acc WHERE user_name = ?", [$request->login]);
            if (!$storedDecrypted || !$storedDecrypted->decrypted_pass) {
                throw ValidationException::withMessages([
                    Fortify::username() => 'User password not found',
                ]);
            }

            // 6. Compare the decrypted passwords
            if ($decryptedInput->decrypted_pass !== $storedDecrypted->decrypted_pass) {
                throw ValidationException::withMessages([
                    Fortify::username() => __('auth.failed'),
                ]);
            }

            // 7. Check if user has a valid designation
            if (is_null($user->designation) || empty($user->designation)) {
                throw ValidationException::withMessages([
                    "wardcode" => "Account not authorized for login",
                ]);
            }

            // 8. Get the ward information to validate wardcode
            $ward = collect(DB::select(
                "SELECT wardcode, wardname, enctype FROM hward WHERE wardcode = ?",
                [$request->wardcode]
            ))->first();

            if (!$ward) {
                throw ValidationException::withMessages([
                    "wardcode" => "Invalid ward selected",
                ]);
            }

            // 9. Authorization logic based on designation and ward
            $isAuthorized = false;
            $errorMessage = 'Unauthorized access to this location';

            switch ($user->designation) {
                case 'admin':
                    // Admin can only login to admin ward
                    if ($request->wardcode === 'ADMIN') {
                        $isAuthorized = true;
                    } else {
                        $errorMessage = 'Unauthorized access';
                    }
                    break;

                case 'csr':
                    // CSR can only login to CSR ward
                    if ($request->wardcode === 'CSR') {
                        $isAuthorized = true;
                    } else {
                        $errorMessage = 'Unauthorized access';
                    }
                    break;

                case 'ward':
                    // Ward users can login to any ward EXCEPT csr and admin
                    if (!in_array($request->wardcode, ['CSR', 'ADMIN'])) {
                        $isAuthorized = true;
                    } else {
                        $errorMessage = 'Unauthorized access';
                    }
                    break;

                default:
                    $errorMessage = 'Invalid user designation. Please contact system administrator.';
                    break;
            }

            if (!$isAuthorized) {
                throw ValidationException::withMessages([
                    "wardcode" => $errorMessage,
                ]);
            }

            // 10. Save pending login data for successful authentication
            session([
                'pending_login_token' => (string) Str::uuid(),
                'pending_wardcode' => $request->wardcode,
                'pending_wardname' => $ward->wardname,
                'pending_enctype' => $ward->enctype ?? null,
            ]);

            // 11. Log successful authentication
            logger()->info('Successful Fortify login', [
                'user' => $user->user_name,
                'designation' => $user->designation,
                'wardcode' => $request->wardcode,
                'wardname' => $ward->wardname,
            ]);

            return $user;
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
