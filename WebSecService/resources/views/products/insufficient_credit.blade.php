@extends('layouts.master')
@section('title', 'Insufficient Credit')
@section('content')
<div class="container mt-4">
    <h1>Insufficient Credit</h1>
    <p>You do not have enough credit to purchase this product. Please add more credit to your account.</p>
    <a href="{{ route('profile', auth()->id()) }}" class="btn btn-primary">Go to Profile</a>
</div>
@endsection