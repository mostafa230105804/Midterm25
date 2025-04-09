@extends('layouts.master')
@section('title', 'Edit Product')
@section('content')

<div class="container mt-4">
    <h1 class="mb-4">{{ isset($product->id) ? 'Edit Product' : 'Add Product' }}</h1>
    <form action="{{ route('products_save', $product->id ?? null) }}" method="POST">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="code" class="form-label">Code:</label>
                    <input type="text" name="code" class="form-control" value="{{ $product->code ?? '' }}" required>
                </div>
                <div class="form-group mb-3">
                    <label for="name" class="form-label">Product Name:</label>
                    <input type="text" name="name" class="form-control" value="{{ $product->name ?? '' }}" required>
                </div>
                <div class="form-group mb-3">
                    <label for="model" class="form-label">Model:</label>
                    <input type="text" name="model" class="form-control" value="{{ $product->model ?? '' }}" required>
                </div>
                <div class="form-group mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" name="price" class="form-control" value="{{ $product->price ?? '' }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea name="description" class="form-control" rows="8" required>{{ $product->description ?? '' }}</textarea>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('products_list') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
    @if(isset($product->id))
        <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
        </form>
    @endif
    <a href="{{ route('products.create') }}" class="btn btn-success form-control">Add Product</a>
</div>

@endsection


