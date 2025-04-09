{{-- resources/views/users/purchased.blade.php --}}

@extends('layouts.master')
@section('title', 'My Purchases')
@section('content')
<div class="container mt-4">
    <h1>My Purchases</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Purchase Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $purchase)
            <tr>
                <td>{{ $purchase->name }}</td>
                <td>${{ number_format($purchase->price, 2) }}</td>
                <td>{{ $purchase->pivot->created_at->format('F j, Y, g:i a') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">No purchases found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="mt-4">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
