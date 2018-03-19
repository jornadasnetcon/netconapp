@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/game_show.css') }}" rel="stylesheet">
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    @if ($user)
                        <a href="{{route('home')}}">Volver a mis partidas</a> | 
                    @else
                        <a href="/login">Login</a> | 
                    @endif

                    <a href="http://netcon.viruk.com/contacto" target="_blank">Contacto</a>
                </div>

                <div id="game_view" class="panel-body" >

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h3>{{$game->title}} {{ $is_full ? '(Completo)' : null }}</h3>

                    <p style="padding: 15px; ">
                    @if ($game->image_name)
                        <img 
                                class="image_game" 
                                src="{{route('storage_get', $game->image_name)}}" 
                                alt="{{$game->title}}" 
                                
                            >
                    @else
                        <img 
                                class="image_game" 
                                src="{{ asset('img/sin_imagen.png') }}" 
                                alt="{{$game->title}}" 
                                
                            >
                    @endif
                    </p>

                    <div class="col-md-8 col-md-offset-2">
                        <p><strong>Organizador</strong>: {{$game->owner->name}}</p>                        
                        @if ($is_owner)
                            <p><strong>Status</strong>: {{$game->approved ? 'Aprobada' : 'Pendiente de aprobar'}}</p>
                        @endif
                        <p>{!! nl2br(e($game->description)) !!}</p>

                        <p><strong>Sistema de Juego</strong>: {{$game->game_system}}</p>

                        <p><strong>Plataforma de Juego</strong>: {{$game->platform}}</p>

                        <p><strong>Hora de inicio</strong>: 
                            {{
                                $game->starting_time ? 
                                $user_timezone ?
                                (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i') : 
                                (new Date($game->starting_time->toDateTimeString()))->format('l j F Y H:i')
                                : null
                            }}
                        </p>

                        <p><strong>Numero de Horas de duracion</strong>: {{$game->duration_hours}}</p>

                        <p><strong>Numero de Sesiones</strong>: {{$game->sessions_number}}</p>
                        
                        <p><strong>Emitida</strong>: {{$game->streamed ? 'Si' : 'No'}}</p>

                        <p><strong>Partida de iniciación</strong>: {{$game->beginner_friendly ? 'Si' : 'No'}}</p>

                        @if ($game->stream_channel)
                        <p><strong>Canal de Emision</strong>: {{$game->stream_channel}}</p>
                        @endif

                        <p><strong>Numero Maximo de Jugadores</strong>: {{$game->maximum_players_number}}</p>

                        <p>
                            <strong>Numero de Jugadores Registrados</strong>: {{$game->signedup_players_number}}
                        </p>

                        @if ($user && $registration_open)

                            @if (!$is_owner && !$is_registered && !$is_full && !$is_partial)
                                <a href="{{route('game_register', ['game' => $game])}}" type="button" class="btn btn-primary center-block" role="button">Registrarse</a>
                            @endif

                            @if ($is_registered)
                                <h3 class="text-center">
                                    <strong>
                                    ¡Estas registrad@!
                                    </strong>
                                </h3>

                                <a href="{{route('game_unregister', ['game' => $game])}}" type="button" class="btn btn-danger center-block" role="button">Abandorar Partida (¡CUIDADO!)</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
