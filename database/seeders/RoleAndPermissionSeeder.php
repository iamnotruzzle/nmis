<?php

namespace Database\Seeders;

use App\Models\Brand;
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

        // create default user
        // $superAdminUser = User::factory()->create([
        //     // 'firstName' => 'super',
        //     // 'middleName' => null,
        //     // 'lastName' => 'admin',
        //     // 'suffix' => null,
        //     'employeeid' => 'sa',
        //     // 'email' => 'sa@sa.com',
        //     'image' => null,
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'designation' => 'admin',
        //     'remember_token' => Str::random(10),
        // ]);

        $admin = User::factory()->create([
            // 'firstName' => 'super',
            // 'middleName' => null,
            // 'lastName' => 'admin',
            // 'suffix' => null,
            'employeeid' => '000037',
            // 'email' => 'sa@sa.com',
            'image' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'designation' => 'admin',
            'remember_token' => Str::random(10),
        ]);

        $csr = User::factory()->create([
            // 'firstName' => 'super',
            // 'middleName' => null,
            // 'lastName' => 'admin',
            // 'suffix' => null,
            'employeeid' => '000123',
            // 'email' => 'sa@sa.com',
            'image' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'designation' => 'csr',
            'remember_token' => Str::random(10),
        ]);

        $wards1 = User::factory()->create([
            // 'firstName' => 'super',
            // 'middleName' => null,
            // 'lastName' => 'admin',
            // 'suffix' => null,
            'employeeid' => '000078',
            // 'email' => 'sa@sa.com',
            'image' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'designation' => 'ward',
            'remember_token' => Str::random(10),
        ]);

        $wards2 = User::factory()->create([
            // 'firstName' => 'super',
            // 'middleName' => null,
            // 'lastName' => 'admin',
            // 'suffix' => null,
            'employeeid' => '001181',
            // 'email' => 'sa@sa.com',
            'image' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'designation' => 'ward',
            'remember_token' => Str::random(10),
        ]);


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

        $admin->assignRole($superAdminRole);
        $csr->assignRole($superAdminRole);
        $wards1->assignRole($superAdminRole);
        $wards2->assignRole($superAdminRole);


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
