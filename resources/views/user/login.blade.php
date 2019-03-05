@extends('layouts.card')
@section('title', 'Sign In')
@section('content')
    <login
        api="{{ route('user.login') }}"
        register="{{ route('user.register') }}"
        redirect="{{ route('index') }}"
        logout="{{ route('user.logout') }}"
        title="@yield('title')"
        @if($errors->any())
            :bag="{{ $errors->toJson() }}"
        @endif
        @if( $email )
            :user="{{ json_encode(compact('email')) }}"
        @endif
        :config="{{ json_encode(config('srp')) }}"
    >
    </login>
@stop
