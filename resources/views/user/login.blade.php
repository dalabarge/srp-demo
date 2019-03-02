@extends('layouts.card')
@section('title', 'Sign In')
@section('content')
    <form action="{{ route('user.login') }}" method="POST">
        @csrf
        <h3 class="uk-margin-remove-top">@yield('title')</h3>
        <p>Please login to your account:</p>
        @include('layouts.partials.errors')
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="email">
                Email
            </label>
            <div class="uk-form-controls">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-envelope"></i></a>
                    <input class="uk-input" id="email" name="email" type="text" required value="{{ $email ?? null }}" @if( empty($email) ) autofocus @endif tabindex="1">
                </div>
            </div>
        </div>
        <div class="uk-margin-bottom">
            <div class="uk-flex uk-flex-middle uk-flex-between">
                <label class="uk-form-label uk-form-required" for="password">
                    Password
                </label>
                <div class="uk-text-right">
                    <a href="#" class="uk-button uk-button-link" tabindex="5">Forgot password?</a>
                </div>
            </div>
            <div class="uk-form-controls uk-margin-small-bottom">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-lock"></i></a>
                    <input class="uk-input" id="password" type="password" required tabindex="2" @if( ! empty($email) ) autofocus @endif>
                </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <button class="uk-button uk-button-primary uk-margin-small-right" type="submit" tabindex="3">Sign In</button>
            <a class="uk-button uk-button-link" href="{{ route("user.register") }}" tabindex="4">Create an Account &rarr;</a>
        </div>
    </form>
@stop
