@include('layouts.partials.message')
@if($errors->any() || session()->has('error'))
  <div uk-alert class="uk-alert uk-alert-danger" style="border:1px solid">
    <a class="uk-alert-close" uk-close></a>
    @if( session()->has('error') )
      <p>{!! session()->pull('error') !!}</p>
    @endif
    @if( $errors->any() )
      <ul class="uk-list">
        @foreach($errors->all() as $error)
          <li>{!! $error !!}</li>
        @endforeach
      </ul>
    @endif
  </div>
@endif
