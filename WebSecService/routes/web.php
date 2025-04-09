<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;

// User Authentication Routes
Route::get('register', [UsersController::class, 'register'])->name('register');
Route::post('register', [UsersController::class, 'doRegister'])->name('do_register');
Route::get('login', [UsersController::class, 'login'])->name('login');
Route::post('login', [UsersController::class, 'doLogin'])->name('do_login');
Route::get('logout', [UsersController::class, 'doLogout'])->name('do_logout');

// User Management Routes
Route::get('users', [UsersController::class, 'list'])->name('users');
Route::get('profile/{user?}', [UsersController::class, 'profile'])->name('profile');
Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users_edit');
Route::post('users/save/{user}', [UsersController::class, 'save'])->name('users_save');
Route::get('users/delete/{user}', [UsersController::class, 'delete'])->name('users_delete');
Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])->name('edit_password');
Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])->name('save_password');
Route::post('users/add_credit/{user}', [UsersController::class, 'addCredit'])->name('users.add_credit');

// Product Management Routes
Route::resource('products', ProductsController::class);
Route::get('/products/edit/{product?}', [ProductsController::class, 'edit'])->name('products_edit');
Route::get('/products/delete/{product}', [ProductsController::class, 'delete'])->name('products_delete');
Route::get('/products', [ProductsController::class, 'list'])->name('products.index');
Route::post('/products/save/{product?}', [ProductsController::class, 'save'])->name('products_save');
Route::get('/products', [ProductsController::class, 'list'])->name('products_list');
Route::get('/products/create', [ProductsController::class, 'create'])->name('products.create');
Route::delete('/products/{product}', [ProductsController::class, 'destroy'])->name('products.destroy');

// Product Purchase Routes
Route::get('/products/insufficient-credit', function () {
    return view('products.insufficient_credit');
})->name('products.insufficient_credit');
Route::get('/profile/purchased', [UsersController::class, 'purchasedProducts'])->name('purchased_products');
Route::get('/purchases', [UsersController::class, 'showPurchases'])->name('users.purchases');
Route::post('/products/purchase/{product}', [ProductsController::class, 'purchaseProduct'])->name('products.purchase');

// Employee Management Routes
// Employee Management Routes
Route::get('/register-employee', [UsersController::class, 'registerEmployee'])->name('register_employee');
Route::post('/register-employee', [UsersController::class, 'storeEmployee'])->name('register_employee.store');

// Role Cleanup Route
Route::get('/roles/cleanup', [UsersController::class, 'cleanUpRoles'])->name('roles.cleanup');

// Customer Management Route
Route::get('/customers', [UsersController::class, 'listCustomers'])
   // ->middleware(['auth', 'role:Employee'])
    ->name('customers.list');

// Miscellaneous Routes
Route::get('/', function () {
    return view('welcome');
});
Route::get('/multable', function (Request $request) {
    $j = $request->number ?? 5;
    $msg = $request->msg;
    return view('multable', compact("j", "msg"));
});
Route::get('/even', function () {
    return view('even');
});
Route::get('/prime', function () {
    return view('prime');
});
Route::get('/test', function () {
    return view('test');
});

Route::post('/products/{product}/update-stock', [ProductsController::class, 'updateStock'])
->name('products.update_stock');