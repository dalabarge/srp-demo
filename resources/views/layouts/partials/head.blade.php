<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="generator" content="{{ gethostname() }}">
    @stack('meta')
    <title>@hasSection('title')@yield('title') – @endif{{ config('app.name') }}</title>
    @hasSection('description')
      <meta name="description" content="@yield('description')">
    @endif
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" />
    @stack('styles')
  </head>
  <body>
