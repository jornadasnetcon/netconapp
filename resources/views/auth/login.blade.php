@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-defaultl" id="panel-login">
                <div class="panel-heading">
                    <h3>BIENVENIDA A LAS {{ env('BRAND_NAME', 'Netcon')}} {{ date('Y' )}}</h3>
                    <p>
                        <img src="{{env('BRAND_LOGO_URL', '/img/logo-netcon.png')}}">
                    </p>
                </div>
                <div class="panel-body">
                     <p>
                        Bienvenida a la <strong>plataforma de gestión de partidas de las <a href="{{ env('BRAND_URL', 'https://www.netconplay.com')}}" title="Visitar {{ env('BRAND_URL', 'https://www.netconplay.com')}}" target="_blank">{{ env('BRAND_NAME', 'Netcon')}}</a>.</strong> Para registrar tus partidas o acceder a las que ya has subido solo tienes que loguearte con tu cuenta.
                    </p>
                    @if (env('DISABLE_LOGIN', false))
                        <p>Acceso temporalmente desactivado</p>
                    @else 
                    <form class="form-horizontal" method="POST" action="{{ url('/login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Email</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">Contraseña</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Recuerdame
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>
                            </div>
                        </div>

                        <p><a class="btn btn-link" href="{{ route('password.request') }}">
                            ¿Te has olvidado de tu contraseña?
                        </a></p>
                        <p>
                            <a href="{{ route('register') }}" class="btn btn-danger">
                                Crear Nueva Usuaria
                            </a>
                        </p>
                    </form>
                    @endif 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
