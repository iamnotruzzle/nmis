<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // create users permissions
        Permission::create(['name' => 'create-users']);
        Permission::create(['name' => 'edit-users']);
        Permission::create(['name' => 'delete-users']);

        // create sa user
        // $superAdminUser = User::factory()->create([
        //     // 'firstName' => 'super',
        //     // 'middleName' => null,
        //     // 'lastName' => 'admin',
        //     // 'suffix' => null,
        //     'employeeid' => '002038',
        //     // 'email' => 'sa@sa.com',
        //     'image' => null,
        //     'password' => '$2y$10$DNZVs1Gvd/nZKXzktIij2Obcm4ghT/.GKp3ltd2K3dKpgsEwwDFjS', // pinakb3t
        //     'designation' => 'admin',
        //     'remember_token' => Str::random(10),
        // ]);

        // // $admin = User::factory()->create([
        // //     // 'firstName' => 'super',
        // //     // 'middleName' => null,
        // //     // 'lastName' => 'admin',
        // //     // 'suffix' => null,
        // //     'employeeid' => '000037',
        // //     // 'email' => 'sa@sa.com',
        // //     'image' => null,
        // //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        // //     'designation' => 'admin',
        // //     'remember_token' => Str::random(10),
        // // ]);

        // // sir edward
        // $edu = User::factory()->create([
        //     // 'firstName' => 'Edward',
        //     // 'middleName' => Q.,
        //     // 'lastName' => 'Ramirez',
        //     // 'suffix' => null,
        //     'employeeid' => '2011-136',
        //     // 'email' => 'sa@sa.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'designation' => 'csr',
        //     'remember_token' => Str::random(10),
        // ]);

        // // sir david
        // $david = User::factory()->create([
        //     // 'firstName' => 'Edward',
        //     // 'middleName' => Q.,
        //     // 'lastName' => 'Ramirez',
        //     // 'suffix' => null,
        //     'employeeid' => '002175',
        //     // 'email' => 'sa@sa.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'designation' => 'csr',
        //     'remember_token' => Str::random(10),
        // ]);

        // // sir jude
        // $jude = User::factory()->create([
        //     // 'firstName' => 'super',
        //     // 'middleName' => null,
        //     // 'lastName' => 'admin',
        //     // 'suffix' => null,
        //     'employeeid' => '002022',
        //     // 'email' => 'sa@sa.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'designation' => 'ward',
        //     'remember_token' => Str::random(10),
        // ]);

        // // sir chris
        // $chris = User::factory()->create([
        //     // 'firstName' => 'super',
        //     // 'middleName' => null,
        //     // 'lastName' => 'admin',
        //     // 'suffix' => null,
        //     'employeeid' => '002021',
        //     // 'email' => 'sa@sa.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'designation' => 'ward',
        //     'remember_token' => Str::random(10),
        // ]);

        // $adminUser = User::factory()->create([
        //     // 'firstName' => 'admin',
        //     // 'middleName' => null,
        //     // 'lastName' => 'admin',
        //     // 'suffix' => null,
        //     'employeeid' => 'admin',
        //     // 'email' => 'admin@admin.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // $user = User::factory()->create([
        //     // 'firstName' => 'user',
        //     // 'middleName' => null,
        //     // 'lastName' => 'user',
        //     // 'suffix' => null,
        //     'employeeid' => 'user',
        //     // 'email' => 'user@user.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        $superAdminUser = User::where('employeeid', '000040')->firstOrFail();

        $superAdminUser->assignRole($superAdminRole);
        // $edu->assignRole($adminRole);
        // $david->assignRole($adminRole);
        // $jude->assignRole($adminRole);
        // $chris->assignRole($adminRole);


        // assign role to the created super-admin user
        // $superAdminUser->assignRole($superAdminRole);

        // assign role to the created admin user
        // $adminUser->assignRole($adminRole);

        // assign role to the created editor user
        // $user->assignRole($userRole);

        // $user->givePermissionTo([
        //     // users
        //     'create-icu-bed',
        //     'create-icu-bed-entry',
        //     'create-non-icu-bed',
        // ]);

    }
}
