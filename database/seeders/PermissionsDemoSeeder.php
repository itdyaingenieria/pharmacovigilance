<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['guard_name' => 'api', 'name' => 'edit clients']);
        Permission::create(['guard_name' => 'api', 'name' => 'delete clients']);
        Permission::create(['guard_name' => 'api', 'name' => 'edit agents']);
        Permission::create(['guard_name' => 'api', 'name' => 'delete agents']);
        Permission::create(['guard_name' => 'api', 'name' => 'export csv']);

        // create roles and assign existing permissions
        $role1 = Role::create(['guard_name' => 'api', 'name' => 'client']);
        $role1->givePermissionTo('edit clients');
        $role1->givePermissionTo('delete clients');

        $role2 = Role::create(['guard_name' => 'api', 'name' => 'agent']);
        $role2->givePermissionTo('edit agents');
        $role2->givePermissionTo('delete agents');
        $role2->givePermissionTo('export csv');

        $role3 = Role::create(['guard_name' => 'api', 'name' => 'admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = \App\Models\User::factory()->create([
            'username'  => 'Example User',
            'email'     => 'tester@example.com',
            'full_name' => 'Example User Full Name',
            'password'  => bcrypt('123456'),
            'user_status_id' => 1,
        ]);
        $user->assignRole($role1);

        $user = \App\Models\User::factory()->create([
            'username'  => 'Example Admin User',
            'email'     => 'admin@example.com',
            'full_name' => 'Example Admin User Full Name',
            'password'  => bcrypt('123456'),
            'user_status_id' => 1,
        ]);
        $user->assignRole($role2);

        $user = \App\Models\User::factory()->create([
            'username'  => 'Example SuperUser',
            'email'     => 'superuser@example.com',
            'full_name' => 'Example SuperUser Full Name',
            'password'  => bcrypt('123456'),
            'user_status_id' => 1,
        ]);
        $user->assignRole($role3);
    }
}
