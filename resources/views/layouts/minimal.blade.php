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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    {{--<link href="{{ asset('css/savagecon.css') }}" rel="stylesheet">--}}
    <script src="{{ asset('js/iframeResizer.contentWindow.min.js') }}" type="text/javascript"></script>
    @yield('style')
</head>
<body>
<script src="{{ asset('js/app.js') }}"></script>
@yield('content')
</body>
</html>
