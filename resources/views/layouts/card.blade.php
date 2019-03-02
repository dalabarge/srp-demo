@include('layouts.partials.head')

<div id="app">
  <div class="uk-container uk-flex uk-flex-center uk-flex-middle" style="height:100vh">
    <div class="uk-width-2-3@s uk-width-1-2@m uk-width-2-5@l">
      <nav class="uk-navbar-container uk-navbar uk-navbar-transparent uk-margin-bottom uk-margin-top" uk-navbar>
        <div class="uk-navbar-center">
          <h1><a href="{{ route('user.login') }}">SRP Demo</a></h1>
        </div>
      </nav>
      <div class="uk-card uk-card-default uk-card-body">
        @yield('content')
      </div>
      <p class="uk-text-center uk-text-small uk-text-muted uk-padding uk-padding-remove-horizontal uk-padding-remove-top">
        &copy; {{ date('Y') }} <a href="https://github.com/artisansdk/srp" target="_blank">Artisans Collaborative</a>. All rights reserved.
      </p>
    </div>
  </div>
</div>

@include('layouts.partials.foot')
