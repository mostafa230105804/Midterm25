@extends('layouts.master')
@section('title', 'Customer List')
@section('content')
<div class="container mt-4">
    <h1>Customers</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection