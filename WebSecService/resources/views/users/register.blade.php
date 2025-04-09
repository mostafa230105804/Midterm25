@extends('layouts.master')
@section('title', 'Register')
@section('content')
<div class="d-flex justify-content-center">
  <div class="card m-4 col-sm-6">
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">
            <strong>Success!</strong> {{ session('success') }}
        </div>
      @endif

      <form action="{{route('do_register')}}" method="post">
      {{ csrf_field() }}
      <div class="form-group">
        @foreach($errors->all() as $error)
          <div class="alert alert-danger">
            <strong>Error!</strong> {{$error}}
          </div>
        @endforeach

        <div class="form-group mb-2">
          <label for="name" class="form-label">Name:</label>
          <input type="text" class="form-control" placeholder="Name" name="name" required value="{{ old('name') }}">
          @error('name')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group mb-2">
          <label for="email" class="form-label">Email:</label>
          <input type="email" class="form-control" placeholder="Email" name="email" required value="{{ old('email') }}">
          @error('email')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group mb-2">
          <label for="password" class="form-label">Password:</label>
          <input type="password" class="form-control" placeholder="Password" name="password" required>
          @error('password')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group mb-2">
          <label for="password_confirmation" class="form-label">Password Confirmation:</label>
          <input type="password" class="form-control" placeholder="Confirm Password" name="password_confirmation" required>
          @error('password_confirmation')
            <div class="alert alert-danger mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group mb-2">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
