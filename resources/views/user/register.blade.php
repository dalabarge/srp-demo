@extends('layouts.card')
@section('title', 'Register an Account')
@section('content')
    <form action="{{ route('user.register') }}" method="POST">
        @csrf
        <h3 class="uk-margin-remove-top">@yield('title')</h3>
        @include('layouts.partials.errors')
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="name">
                Name
            </label>
            <div class="uk-form-controls">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-user"></i></a>
                    <input class="uk-input" id="name" name="name" type="text" required value="{{ $name }}" @if( empty($name) ) autofocus @endif tabindex="1">
                </div>
            </div>
        </div>
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="email">
                Email
            </label>
            <div class="uk-form-controls">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-envelope"></i></a>
                    <input class="uk-input" id="email" name="email" type="text" required value="{{ $email }}" @if( ! empty($name) && empty($email) ) autofocus @endif tabindex="2">
                </div>
            </div>
        </div>
        <div class="uk-margin-bottom">
            <label class="uk-form-label uk-form-required" for="password">
                Password
            </label>
            <div class="uk-form-controls uk-margin-small-bottom">
                <div class="uk-inline uk-width-1-1">
                    <a class="uk-form-icon uk-form-icon-flip"><i class="fas fa-lock"></i></a>
                    <input class="uk-input" id="password" type="password" required tabindex="3" @if( ! empty($name) && ! empty($email) ) autofocus @endif>
                </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-middle uk-flex-between">
            <button class="uk-button uk-button-primary uk-margin-small-right" type="submit" tabindex="4">Create Account</button>
            <a class="uk-button uk-button-link" href="{{ route("user.login") }}" tabindex="5">Back to Login &rarr;</a>
        </div>
    </form>
@stop
