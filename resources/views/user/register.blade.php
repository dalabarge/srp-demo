@extends('layouts.card')
@section('title', 'Register an Account')
@section('content')
    <register
        api="{{ route('user.register') }}"
        login="{{ route('user.login') }}"
        title="@yield('title')"
        @if($errors->any())
            :bag="{{ $errors->toJson() }}"
        @endif
        @if( $name || $email )
            :user="{{ json_encode(compact('name', 'email')) }}"
        @endif
        :config="{{ json_encode(config('srp')) }}"
    >
    </register>
@stop
