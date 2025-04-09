@extends('layouts.master')

@section('title', 'Users and Purchased Products')

@section('content')
<div class="container mt-4">
    <h1>Users List</h1>
    <a href="{{ route('customers.list') }}" class="btn btn-primary mb-3">View Customers</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ route('profile', $user->id) }}" class="btn btn-info">View Profile</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h1 class="mt-5">Purchased Products</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Purchase Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchasedProducts as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>${{ number_format($product->price, 2) }}</td>
                <td>{{ $product->pivot->created_at->format('F j, Y, g:i a') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
