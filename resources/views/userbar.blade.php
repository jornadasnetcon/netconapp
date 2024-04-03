@extends('layouts.minimal')

@section('content')
@auth
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
    <div class="user-bar">
        <div class="wrapper">
            {{ Auth::user()->name }} | <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cerrar sesión
            </a>
        </div>
    </div>
@endauth
@endsection