<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Ensure roles and permissions exist
        Permission::findOrCreate('add_products');
        Permission::findOrCreate('edit_products');
        Permission::findOrCreate('delete_products');
        Permission::findOrCreate('show_users');
        Permission::findOrCreate('edit_users');
        Permission::findOrCreate('delete_users');
        Permission::findOrCreate('admin_users');

        Role::findOrCreate('Admin')->givePermissionTo(Permission::all());
        Role::findOrCreate('Employee')->givePermissionTo([
            'add_products', 'edit_products', 'delete_products',
            'show_users', 'edit_users'
        ]);
        Role::findOrCreate('Customer'); // No special permissions
    }
}
