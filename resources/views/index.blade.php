@extends('layouts.card')
@section('title', 'Home')
@section('content')
    @include('layouts.partials.errors')
    <p>
        <strong>Secure Remote Password (SRP-6a)</strong> is an improvement over the
        traditional protocol of sending plain text passwords over the wire. Even if
        using HTTPS for transmission, the fact the password ever leaves the client puts
        the server at risk for leaking information it never actually needs to begin
        with.
    </p>

    <div class="uk-flex uk-flex-middle uk-flex-between">
        @guest
            <a href="{{ route('user.register') }}" class="uk-button uk-button-primary">Register Account</a>
            <a href="{{ route('user.login') }}" class="uk-button uk-button-link">Go to Sign In &rarr;</a>
        @else
            <div class="uk-flex uk-flex-middle">
                <i class="fas faw fa-user-circle fa-2x uk-text-primary uk-margin-small-right"></i>
                <strong>{{ auth()->user()->name }}</strong>
            </div>
            <a href="{{ route('user.logout') }}" class="uk-button uk-button-link">Log Out &rarr;</a>
        @endif
    </div>
@stop
