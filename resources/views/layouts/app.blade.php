<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="{{ asset('img/symbol.png') }}">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title')</title>

  <!-- Scripts -->
  <script src="{{ mix('js/app.js') }}" defer></script>

  <!-- Styles -->
  <link href="{{ mix('css/app.css') }}" rel="stylesheet">
  <link href="{{ mix('css/layouts.css') }}" rel="stylesheet">

  <!-- Extend head -->
  @yield('head')
</head>

<body>
  <div id="app">
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="{{url('/')}}"><img style="max-width:100%;height:auto;" src="{{ asset('img/logo.png') }}"/></a>
      @auth<div class="col-8 text-right text-white">
        <!-- name -->
        <span>{{Auth::user()->name}}</span>
      </div>@endauth
      <ul class="navbar-nav px-5">
        <li class="nav-item text-nowrap">
          @guest
          <a class="nav-link" href="{{ route('login') }}">ログイン</a>
          @else
          <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault();
                                  document.getElementById('logout-form').submit();">
            {{ __('ログアウト') }}
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
          @endguest
        </li>
      </ul>
    </nav>
    <div class="container-fluid">
      <div class="row">
        @auth
        <nav class="col-md-2 bg-light sidebar">
          <div class="sidebar-sticky">
            @include('layouts.navi')
          </div>
        </nav>
        @endauth
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
          @if($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          @if(Session::has('success'))
          <div class='alert alert-success'>
            {{ Session::get('success') }}
          </div>
          @endif
          @yield('content')
        </main>
      </div>
    </div>
  </div>
</body>

</html>