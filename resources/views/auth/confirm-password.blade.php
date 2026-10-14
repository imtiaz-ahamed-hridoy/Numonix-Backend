@extends('auth.app')

@section('title', 'Confirm Password')

@section('content')
    <div class="wrap-login100 p-0">
        <div class="card-body">
            <form class="login100-form validate-form" action="{{ route('password.confirm') }}" method="post">
                @csrf
                <div class="text-center">
                    <span class="login100-form-title">
                        Confirm Password
                    </span>
                    <p class="text-muted">Please confirm your password before continuing</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password:</label>
                    <input class="form-control" id="password" placeholder="Enter your password" type="password"
                        name="password">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="container-login100-form-btn">
                    <button type="submit" class="login100-form-btn btn-primary">Confirm Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
