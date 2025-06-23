<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{asset('img/favicon.png')}}">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/2023.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    {{--<link href="{{ asset('css/savagecon.css') }}" rel="stylesheet">--}}
    <script src="{{ asset('js/iframeResizer.contentWindow.min.js') }}" type="text/javascript"></script>
    @yield('style')
</head>
<body>
    <div id="app">
        @include('cookieConsent::index')
        @if (config('app.menu', false))
        <nav class="navbar navbar-default navbar-static-top" style="background: white;">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @guest
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                    Menu <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{route('game_list')}}">Listado de partidas </a>
                                    </li>
                                    <li>
                                        <a href="{{route('talk_list')}}">Listado de charlas</a>
                                    </li>
                                    {{--<li>--}}
                                        {{--<a href="http://www.viruk.com/netconcal/">Calendario de Partidas </a>--}}
                                    {{--</li>--}}
                                    <li>
                                        <a href="{{ env("CONTACT_URL", '#') }}" title="Ponte en contacto con nosotros" target="_blank">Contacto</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('/login') }}">
                                            Login
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{route('home')}}">Mi perfil </a>
                                    </li>
                                    <li>
                                        <a href="{{route('game_list')}}">Listado de partidas </a>
                                    </li>

                                    <li>
                                        <a href="{{route('talk_list')}}">Listado de charlas</a>
                                    </li>
                                    {{--<li>--}}
                                        {{--<a href="http://www.viruk.com/netconcal/">Calendario de Partidas </a>--}}
                                    {{--</li>--}}

                                    @if ($user->isTester() || env('GAME_REGISTRATION_ENABLED', false))
                                    <li>
                                        <a href="{{route('game_post')}}">Nueva partida </a>
                                    </li>
                                    @endif

                                    <li>
                                        <a href="{{ env('CONTACT_URL', '#') }}" title="Ponte en contacto con nosotros" target="_blank">Contacto</a>
                                    </li>

                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Cerrar sesión
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @endif

        @yield('content')

        <footer class="footer">
            <a href="{{ env('LEGAL_URL', '#') }}" target="_blank">Politica de privacidad</a>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/2023.js') }}" type="text/javascript"></script>
    <!--<script src="{{ asset('js/2024.js') }}" type="text/javascript"></script>-->
    @yield('scripts')
</body>
</html>
