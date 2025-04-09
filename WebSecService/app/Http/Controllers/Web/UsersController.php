<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Artisan;
use App\Http\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller {

    use ValidatesRequests;

    public function list()
    {
        $users = User::all(); // Fetch all users
        $purchasedProducts = auth()->user()->purchasedProducts()->withPivot('created_at')->get(); // Removed 'quantity'

        return view('users.list', compact('users', 'purchasedProducts'));
    }

    public function register(Request $request) {
        return view('users.register');
    }

    public function doRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:5|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|string|in:Admin,Employee,Customer', // Restrict to valid roles
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->credit = 0.00; // Initialize credit for all users
        $user->save();

        // Assign only the selected role and remove any other roles
        $user->syncRoles([$request->role]);

        return redirect()->route('login')->with('success', 'Registration successful. You can now log in.');
    }

    public function login(Request $request) {
        return view('users.login');
    }

    public function doLogin(Request $request) {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->back()->withInput($request->input())->withErrors('Invalid login information.');
        }

        $user = User::where('email', $request->email)->first();
        Auth::setUser($user);

        return redirect('/');
    }

    public function doLogout(Request $request) {
        Auth::logout();
        return redirect('/');
    }

    public function profile(User $user = null)
    {
        $user = $user ?? auth()->user();
        $purchasedProducts = $user->purchasedProducts()->withPivot('created_at')->paginate(10); // Removed 'quantity'

        return view('users.profile', compact('user', 'purchasedProducts'));
    }

    public function edit(Request $request, User $user = null) {
        $user = $user ?? auth()->user();
        if (auth()->id() != $user?->id) {
            if (!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }
    
        $roles = [];
        foreach (Role::all() as $role) {
            $role->taken = ($user->hasRole($role->name));
            $roles[] = $role;
        }

        $permissions = [];
        $directPermissionsIds = $user->permissions()->pluck('id')->toArray();
        foreach (Permission::all() as $permission) {
            $permission->taken = in_array($permission->id, $directPermissionsIds);
            $permissions[] = $permission;
        }      

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    
public function save(Request $request, User $user = null)
{
    if (!auth()->user()->hasPermissionTo('admin_users')) {
        return redirect()->back()->withErrors("Only admins can create or manage employees.");
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . ($user->id ?? 'NULL'),
        'password' => $user ? 'nullable|min:8' : 'required|min:8',
        'role' => 'required|string|in:Admin,Employee,Customer', // Restrict to valid roles
    ]);

    if (!$user) {
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
    }

    $user->name = $request->name;
    $user->save();

    // Prevent conflicting roles
    if ($request->role === 'Admin' && $user->hasRole('Customer')) {
        $user->removeRole('Customer');
    } elseif ($request->role === 'Customer' && $user->hasRole('Admin')) {
        $user->removeRole('Admin');
    }

    // Assign only the selected role
    $user->syncRoles([$request->role]);

    return redirect(route('profile', ['user' => $user->id]))->with('success', 'User saved successfully.');
}

    public function delete(Request $request, User $user) {
        if (!auth()->user()->hasPermissionTo('delete_users')) abort(401);

        $user->delete();
        return redirect()->route('users');
    }

    public function editPassword(Request $request, User $user = null) {
        $user = $user ?? auth()->user();
        if (auth()->id() != $user?->id) {
            if (!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }

        return view('users.edit_password', compact('user'));
    }

    public function savePassword(Request $request, User $user) {
        if (auth()->id() == $user?->id) {
            $this->validate($request, [
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);

            if (!Auth::attempt(['email' => $user->email, 'password' => $request->old_password])) {
                Auth::logout();
                return redirect('/');
            }
        } else if (!auth()->user()->hasPermissionTo('edit_users')) {
            abort(401);
        }

        $user->password = bcrypt($request->password); // Secure
        $user->save();

        return redirect(route('profile', ['user' => $user->id]));
    }

    public function purchasedProducts() {
        $user = auth()->user();

        // Ensure the user is a customer
        if (!$user->hasRole('Customer')) {
            return redirect()->back()->withErrors('Only customers can view purchased products.');
        }

        $purchasedProducts = $user->purchasedProducts()->withPivot('created_at')->paginate(10);

        return view('users.purchased', compact('purchasedProducts'));
    }

    // Employee-specific methods

    // Employee can list all customers associated with their account
    public function listCustomers()
    {
        $user = auth()->user();

        // Ensure the logged-in user has the "Employee" role
        if (!$user->hasRole('Employee')) {
            return redirect()->back()->withErrors('Only employees can view customers.');
        }

        // Fetch only users with the "Customer" role
        $customers = User::role('Customer')->get();

        return view('customers.list', compact('customers'));
    }

    // Employee can add credit to customer accounts
    public function addCredit(Request $request, User $user) {
        $request->validate([
            'credit_amount' => 'required|numeric|min:1',
        ]);

        // Add the credit amount to the user's current credit
        $user->credit += $request->credit_amount;
        $user->save();

        return redirect()->back()->with('success', 'Credit added successfully.');
    }

    // Employee can add, edit, and delete products
    public function addProduct(Request $request) {
        if (!auth()->user()->hasRole('Employee')) {
            abort(401); // Unauthorized
        }

        // Code to add product logic here
    }

    public function editProduct(Request $request, $productId) {
        if (!auth()->user()->hasRole('Employee')) {
            abort(401); // Unauthorized
        }

        // Code to edit product logic here
    }

    public function deleteProduct(Request $request, $productId) {
        if (!auth()->user()->hasRole('Employee')) {
            abort(401); // Unauthorized
        }

        // Code to delete product logic here
    }

    public function updateProductStock(Request $request, $productId)
    {
        $user = auth()->user();

        // Ensure the logged-in user has the "Employee" role
        if (!$user->hasRole('Employee')) {
            return redirect()->back()->withErrors('Only employees can update product stock.');
        }

        $request->validate([
            'available_items' => 'required|integer|min:0', // Ensure the value is a non-negative integer
        ]);

        $product = Product::findOrFail($productId);
        $product->available_items = $request->available_items;
        $product->save();

        return redirect()->back()->with('success', 'Product stock updated successfully.');
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        
        // Sync roles (remove old roles and add new ones)
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('users_list')->with('success', 'User updated successfully');
    }

    public function registerEmployee()
    {
        // Ensure only administrators can access this method
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('users.register_employee');
    }

    public function storeEmployee(Request $request)
    {
        // Ensure only administrators can access this method
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $employee = new User();
        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->password = bcrypt($request->password);
        $employee->save();

        // Assign only the "Employee" role and remove any other roles
        $employee->syncRoles(['Employee']);

        return redirect()->route('users')->with('success', 'Employee account created successfully.');
    }

    public function cleanUpRoles()
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->hasRole('Admin') && $user->hasRole('Customer')) {
                $user->removeRole('Customer'); // Remove Customer role from Admins
            }

            if ($user->hasRole('Employee') && $user->hasRole('Customer')) {
                $user->removeRole('Customer'); // Remove Customer role from Employees
            }
        }

        return redirect()->back()->with('success', 'Role cleanup completed.');
    }

    public function purchaseProduct(Request $request, $productId)
    {
        $user = auth()->user();

        // Ensure the user has the "purchase_products" permission
        if (!$user->can('purchase_products')) {
            return redirect()->back()->withErrors('You do not have permission to purchase products.');
        }

        $product = Product::findOrFail($productId);

        // Check if the product is in stock
        if ($product->available_items <= 0) {
            return redirect()->back()->withErrors('This product is out of stock.');
        }

        // Check if the user has enough credit
        if ($user->credit < $product->price) {
            return redirect()->route('products.insufficient_credit');
        }

        // Deduct the product price from the user's credit
        $user->credit -= $product->price;
        $user->save();

        // Decrement the available items
        $product->available_items -= 1;
        $product->save();

        // Record the purchase in the purchases table
        $user->purchasedProducts()->attach($productId, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('products_list')->with('success', 'Product purchased successfully!');
    }

    public function showPurchases()
    {
        $user = auth()->user();

        // Fetch the user's purchases
        $purchases = $user->purchasedProducts()->withPivot('created_at')->paginate(10);

        return view('users.purchases', compact('purchases'));
    }

    public function assignCustomerRoleToUser()
    {
        $user = User::find(1); // Replace 1 with the correct user ID

        if (!$user) {
            dd('User not found. Please ensure the user exists in the database.');
        }

        $user->assignRole('Customer');
    }
}