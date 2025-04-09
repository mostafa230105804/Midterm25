@extends('layouts.master')
@section('title', 'Products')
@section('content')
<div class="row mt-2">
    <div class="col col-10">
        <h1>Products</h1>
    </div>
    <div class="col col-2">
        @can('add_products')
        <a href="{{ route('products.create') }}" class="btn btn-success form-control">Add Product</a>
        @endcan
    </div>
</div>

<form method="GET" action="{{ route('products_list') }}">
    <div class="row">
        <div class="col col-sm-2">
            <input name="keywords" type="text" class="form-control" placeholder="Search Keywords" value="{{ request()->keywords }}" />
        </div>
        <div class="col col-sm-2">
            <input name="min_price" type="number" class="form-control" placeholder="Min Price" value="{{ request()->min_price }}"/>
        </div>
        <div class="col col-sm-2">
            <input name="max_price" type="number" class="form-control" placeholder="Max Price" value="{{ request()->max_price }}"/>
        </div>
        <div class="col col-sm-2">
            <select name="order_by" class="form-select">
                <option value="" disabled selected>Order By</option>
                <option value="name" {{ request()->order_by=="name" ? "selected" : "" }}>Name</option>
                <option value="price" {{ request()->order_by=="price" ? "selected" : "" }}>Price</option>
            </select>
        </div>
        <div class="col col-sm-2">
            <select name="order_direction" class="form-select">
                <option value="ASC" {{ request()->order_direction=="ASC" ? "selected" : "" }}>ASC</option>
                <option value="DESC" {{ request()->order_direction=="DESC" ? "selected" : "" }}>DESC</option>
            </select>
        </div>
        <div class="col col-sm-1">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
        <div class="col col-sm-1">
            <a href="{{ route('products_list') }}" class="btn btn-danger">Reset</a>
        </div>
    </div>
</form>

@foreach($products as $product)
    <div class="card mt-2">
        <div class="card-body">
            <div class="row">
                <div class="col col-sm-12 col-lg-4">
                    <img src="{{ asset('images/' . $product->photo) }}" class="img-thumbnail" alt="{{ $product->name }}" width="100%">
                </div>
                <div class="col col-sm-12 col-lg-8 mt-3">
                    <div class="row mb-2">
                        <div class="col-8">
                            <h3>{{ $product->name }}</h3>
                        </div>
                        @can('edit_products')
                        <div class="col col-2">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning form-control">Edit</a>
                        </div>
                        @endcan
                        @can('delete_products')
                        <div class="col col-2">
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger form-control" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                        @endcan
                    </div>

                    <table class="table table-striped">
                        <tr><th>Name</th><td>{{ $product->name }}</td></tr>
                        <tr><th>Model</th><td>{{ $product->model }}</td></tr>
                        <tr><th>Code</th><td>{{ $product->code }}</td></tr>
                        <tr><th>Price</th><td>${{ number_format($product->price, 2) }}</td></tr>
                        <tr><th>Description</th><td>{{ $product->description }}</td></tr>
                        <tr><th>Available Items</th><td>{{ $product->available_items }}</td></tr>
                    </table>

                    @if(auth()->user()->hasRole('Employee'))
                    <form action="{{ route('products.update_stock', $product->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="available_items">Update Stock:</label>
                            <input type="number" name="available_items" class="form-control" min="0" value="{{ $product->available_items }}" required>
                        </div>
                        <button type="submit" class="btn btn-warning mt-2">Update Stock</button>
                    </form>
                    @endif

                    @if(auth()->user()->hasRole('Customer'))
                        @if($product->available_items > 0 && auth()->user()->credit >= $product->price)
                            <form action="{{ route('products.purchase', $product->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary">Buy</button>
                            </form>
                        @elseif(auth()->user()->credit < $product->price)
                            <button class="btn btn-secondary" disabled>Insufficient Credit</button>
                        @else
                            <button class="btn btn-secondary" disabled>Out of Stock</button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
