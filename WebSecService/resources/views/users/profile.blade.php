@extends('layouts.master')
@section('title', 'User Profile')
@section('content')
<div class="row">
    <div class="m-4 col-12 col-sm-6">
        <table class="table table-striped">
            <tr>
                <th>Name</th><td>{{$user->name}}</td>
            </tr>
            <tr>
                <th>Email</th><td>{{$user->email}}</td>
            </tr>
            <tr>
                <th>Roles</th>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{$role->name}}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>Permissions</th>
                <td>
                    @if(isset($permissions) && count($permissions) > 0)
                        @foreach($permissions as $permission)
                            <span class="badge bg-success">{{$permission->display_name}}</span>
                        @endforeach
                    @else
                        <span class="text-muted">No permissions assigned</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Credit</th>
                <td>${{ number_format($user->credit, 2) }}</td>
            </tr>
            <tr>
                <th>Account Created</th>
                <td>
                    @if($user->created_at)
                        {{ $user->created_at->format('F j, Y, g:i a') }}
                    @else
                        <span class="text-muted">Not available</span>
                    @endif
                </td>
            </tr>
        </table>

        @if(auth()->user()->hasRole('Employee') && auth()->id() !== $user->id)
        <form action="{{ route('users.add_credit', $user->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="credit_amount">Add Credit:</label>
                <input type="number" name="credit_amount" class="form-control" min="1" placeholder="Enter amount" required>
            </div>
            <button type="submit" class="btn btn-warning mt-2">Add Credit</button>
        </form>
        @endif

        <div class="row">
            <div class="col col-6"></div>
            @if(auth()->user()->hasPermissionTo('admin_users') || auth()->id() == $user->id)
            <div class="col col-4">
                <a class="btn btn-primary" href='{{route('edit_password', $user->id)}}'>Change Password</a>
            </div>
            @endif
            @if(auth()->user()->hasPermissionTo('edit_users') || auth()->id() == $user->id)
            <div class="col col-2">
                <a href="{{route('users_edit', $user->id)}}" class="btn btn-success form-control">Edit</a>
            </div>
            @endif
        </div>

        @if(auth()->user()->hasRole('Admin'))
            <a href="{{ route('register_employee') }}" class="btn btn-primary">Register Employee</a>
        @endif

       
    </div>
</div>
@endsection
