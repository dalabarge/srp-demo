@extends('layouts.card')
@section('title', 'Sign In')
@section('content')
    <login
        api="{{ route('user.login') }}"
        register="{{ route('user.register') }}"
        redirect="{{ route('index') }}"
        title="@yield('title')"
        @if(session()->has('error'))
            error="{{ session()->pull('error') }}"
        @endif
        @if( $email )
            :user="{{ json_encode(compact('email')) }}"
        @endif
        :config="{{ json_encode(config('srp')) }}"
    >
    </login>
@stop
