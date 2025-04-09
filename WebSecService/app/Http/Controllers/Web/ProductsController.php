<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductsController extends Controller {

	use ValidatesRequests;

	public function __construct()
    {
        $this->middleware('auth:web')->except('list');
    }

	public function list(Request $request)
	{
	    $query = Product::query();

	    // Apply filters
	    if ($request->filled('keywords')) {
	        $query->where('name', 'like', '%' . $request->keywords . '%');
	    }
	    if ($request->filled('min_price')) {
	        $query->where('price', '>=', $request->min_price);
	    }
	    if ($request->filled('max_price')) {
	        $query->where('price', '<=', $request->max_price);
	    }

	    // Apply sorting
	    if ($request->filled('order_by') && $request->filled('order_direction')) {
	        $query->orderBy($request->order_by, $request->order_direction);
	    }

	    // Get the products
	    $products = $query->get();

	    return view('products.list', compact('products'));
	}

	public function index()
	{
	    $products = Product::all(); // Fetch all products
	    return view('products.list', compact('products')); // Return the product list view
	}

	public function edit($productId = null)
	{
	    $product = $productId ? Product::findOrFail($productId) : new Product(); // Fetch the product if editing, or create a new instance for adding
	    return view('products.edit', compact('product'));
	}

	public function create()
	{
	    // Pass an empty product object to the view for adding a new product
	    $product = new \stdClass();
	    $product->id = null;
	    $product->code = '';
	    $product->name = '';
	    $product->model = '';
	    $product->price = '';
	    $product->description = '';

	    return view('products.edit', compact('product'));
	}

	public function save(Request $request, $productId = null)
	{
	    $request->validate([
	        'code' => 'required|string|max:255',
	        'name' => 'required|string|max:255',
	        'model' => 'required|string|max:255',
	        'price' => 'required|numeric|min:0',
	        'description' => 'required|string|max:1000',
	    ]);

	    $product = $productId ? Product::findOrFail($productId) : new Product();
	    $product->code = $request->code;
	    $product->name = $request->name;
	    $product->model = $request->model;
	    $product->price = $request->price;
	    $product->description = $request->description;
	    $product->save();

	    return redirect()->route('products_list')->with('success', 'Product saved successfully.');
	}

	public function delete($productId)
	{
	    $product = Product::findOrFail($productId);
	    $product->delete();

	    return redirect()->back()->with('success', 'Product deleted successfully.');
	}
	
	public function purchase(Request $request, $productId)
	{
		$request->validate([
		    'quantity' => 'required|integer|min:1',
		]);

		// Get the authenticated user and the product being purchased
		$user = auth()->user();
		$product = Product::find($productId);

		// Check if the product exists and is in stock
		if (!$product || $product->stock <= 0) {
			return redirect()->back()->withErrors('Product is out of stock.');
		}

		// Check if the user has enough credit to make the purchase
		if ($user->credit < $product->price) {
			return redirect()->back()->withErrors('You do not have enough credit to make this purchase.');
		}

		// Deduct the product price from the user's credit and decrease the stock
		$user->credit -= $product->price;
		$product->stock -= $request->input('quantity', 1);
		if ($product->stock < 0) {
		    return redirect()->back()->withErrors('Not enough stock available.');
		}
		$product->save();

		// Save changes
		$user->save();

		// Record the purchase (optional: create a separate purchase table or use a pivot table)
		$user->purchasedProducts()->attach($productId);

		return redirect()->back()->with('success', 'Purchase successful!');
	}

	public function purchaseProduct(Request $request, $productId)
	{
	    $product = Product::findOrFail($productId);

	    if ($product->available_items <= 0) {
	        return redirect()->back()->withErrors('This product is out of stock.');
	    }

	    $customer = auth()->user();
	    if ($customer->credit < $product->price) {
	        return redirect()->route('products.insufficient_credit')->withErrors('Insufficient credit to purchase this product.');
	    }

	    $customer->credit -= $product->price;
	    $customer->save();

	    $product->available_items -= 1;

	    // Debugging: Log the updated available_items value
	    \Log::info('Updated available_items for product ID ' . $productId . ': ' . $product->available_items);

	    $product->save();

	    $customer->purchasedProducts()->attach($productId, ['created_at' => now()]);

	    return redirect()->back()->with('success', 'Product purchased successfully.');
	}

	public function addProduct(Request $request)
	{
	    $request->validate([
	        'name' => 'required|string|max:255',
	        'price' => 'required|numeric|min:0',
	        'available_items' => 'required|integer|min:0',
	    ]);

	    $product = new Product();
	    $product->name = $request->name;
	    $product->price = $request->price;
	    $product->available_items = $request->available_items;
	    $product->save();

	    return redirect()->back()->with('success', 'Product added successfully.');
	}

	public function editProduct(Request $request, $productId)
	{
	    $product = Product::findOrFail($productId);

	    $request->validate([
	        'name' => 'required|string|max:255',
	        'price' => 'required|numeric|min:0',
	        'available_items' => 'required|integer|min:0',
	    ]);

	    $product->name = $request->name;
	    $product->price = $request->price;
	    $product->available_items = $request->available_items;
	    $product->save();

	    return redirect()->back()->with('success', 'Product updated successfully.');
	}

	public function deleteProduct(Request $request, $productId)
	{
	    $product = Product::findOrFail($productId);
	    $product->delete();

	    return redirect()->back()->with('success', 'Product deleted successfully.');
	}

	public function updateStock(Request $request, $productId)
	{
	    // Debugging: Log the incoming request
	    \Log::info('Update Stock Request:', $request->all());

	    $request->validate([
	        'available_items' => 'required|integer|min:0', // Ensure the value is a non-negative integer
	    ]);

	    $product = Product::find($productId);

	    // Debugging: Log the product details
	    if (!$product) {
	        \Log::error('Product not found with ID: ' . $productId);
	        return redirect()->back()->withErrors('Product not found.');
	    }

	    \Log::info('Product Found:', $product->toArray());

	    $product->available_items = $request->available_items;
	    $product->save();

	    return redirect()->back()->with('success', 'Product stock updated successfully.');
	}

	public function show($productId)
	{
	    $product = Product::findOrFail($productId);
	    return view('products.show', compact('product'));
	}

	public function destroy($productId)
	{
	    // Find the product by ID
	    $product = Product::findOrFail($productId);

	    // Delete the product
	    $product->delete();

	    // Redirect back with a success message
	    return redirect()->route('products_list')->with('success', 'Product deleted successfully.');
	}
}

//Role::create(['name' => 'Customer']);
