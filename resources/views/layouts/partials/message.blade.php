@if(session()->has('message'))
  <div uk-alert class="uk-alert uk-alert-success" style="border:1px solid">
    <a class="uk-alert-close" uk-close></a>
    @if( session()->has('message') )
      <p>{!! session()->pull('message') !!}</p>
    @endif
  </div>
@endif
