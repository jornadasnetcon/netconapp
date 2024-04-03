@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    @if ($user)
                        <a href="{{route('home')}}">Volver a mi perfil</a> |
                        @if ($user->isAdmin() || $is_owner)
                            <a href="{{route('game_edit', ['game' => $game], true)}}">Editar</a> |
                        @endif
                        @if (!$game->isApproved() && $user->isAdmin())
                            <a href="{{route('game_approve', ['game' => $game], true)}}">Aprobar</a> |
                        @endif
                    @else
                        <a href="/login">Login</a> |
                    @endif

                    <a href="{{ env('CONTACT_URL', '#') }}" target="_blank">Contacto</a>
                </div>

                <div id="game_view" class="panel-body" >

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h3>{{$game->title}} {{ $is_full ? '(Completo)' : null }}</h3>

                    <p style="padding: 15px; text-align: center;">
                    @if ($game->image_name)
                        <img
                                class="image_game"
                                src="{{route('storage_get', $game->image_name)}}"
                                alt="{{$game->title}}"
                                style="max-width: 100%;"
                            >
                    @else
                        <img
                                class="image_game"
                                src="{{ asset('img/sin_imagen.jpg') }}"
                                alt="{{$game->title}}"
                                style="max-width: 100%;"

                            >
                    @endif
                    </p>

                    <div class="col-md-8 col-md-offset-2">
                        <p><strong>Organizadora</strong>: {{$game->owner->name}}</p>
                        @if ($is_owner)
                            <p><strong>Estado</strong>: {{$game->approved ? 'Aprobada' : 'Pendiente de aprobar'}}</p>
                        @endif
                        <p style="word-break: normal;">{!! nl2br(e($game->description)) !!}</p>


                        <p><strong>Herramientas de seguridad</strong>: {{$game->safety_tools}}</p>

                        <p><strong>Aviso de contenido sensible</strong>: {{$game->content_warning}}</p>

                        <p><strong>Sistema de juego</strong>: {{$game->game_system}}</p>

                        <p><strong>Plataforma de juego</strong>: {{$game->platform}}</p>
                        <p><strong>Horario de inicio</strong>:
                            {{
                                $game->starting_time
                                    ? $user_timezone
                                        ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                        : (new Date($game->starting_time->toDateTimeString()))->format('l j F Y H:i')
                                    : null
                            }}
                        </p>

                        <p><strong>Número de horas de duración</strong>: {{$game->duration_hours}}</p>

                        <!--<p><strong>Número de sesiones</strong>: {{$game->sessions_number}}</p>-->

                        <p><strong>Emitida</strong>: {{$game->streamed ? 'Si' : 'No'}}</p>

                        <p><strong>Partida de iniciación</strong>: {{$game->beginner_friendly ? 'Si' : 'No'}}</p>

                        @if ($game->owner->about)
                            <p><strong>Sobre la directora</strong></p>
                            <p style="word-break: normal;">{!! nl2br(e($game->owner->about)) !!}</p>
                        @endif

                        @if ($game->stream_channel)
                        <p><strong>Canal de emision</strong>: {{$game->stream_channel}}</p>
                        @endif

                        <p><strong>Número máximo de jugadoras</strong>: {{$game->maximum_players_number}}</p>

                        <p>
                            <strong>Número de jugadoras registradas</strong>: {{$game->signedup_players_number}}
                        </p>

                        @if ($is_full)
                            <h3 class="text-center text-info">
                                <strong>
                                La partida está llena
                                </strong>
                            </h3>
                        @endif

                        @if ($user && ($registration_open || $is_admin))
                            @if ($game->canRegister($user))
                                <a href="{{route('game_register', ['game' => $game])}}" type="button" class="btn btn-primary center-block" role="button">Registrarse</a>
                            @elseif ($game->canWaitlist($user))
                                <a href="{{route('game_register_waitlist', ['game' => $game])}}" type="button" class="btn btn-warning center-block" role="button">Apuntarse a la lista de espera</a>
                            @elseif ($registered = $user->isBusy($game))
                                <div style="background-color: #dcdcdc; padding: 20px; text-align:center">Ya estás inscrita en otra partida en el mismo horario:<br/> <b><?= $registered->title ?></b></div>
                                
                            @endif

                            @if ($is_registered)
                                <h3 class="text-center">
                                    <strong>
                                    ¡Estás registrada!
                                    </strong>
                                </h3>
                                @if ($game->session_no == 1)
                                    <a href="{{route('game_unregister', ['game' => $game])}}" type="button" class="btn btn-danger center-block" role="button">Abandonar partida (¡CUIDADO!)</a>
                                @endif
                            @endif

                            @if ($is_waitlisted)
                                <h3 class="text-center">
                                    <strong>
                                    ¡Estás en la lista de espera!
                                    </strong>
                                </h3>

                                <p>Si queda un espacio libre y estas la primera en la lista de espera, te registraremos en la partida automáticamente y te enviaremos un correo para avisarte.</p>
                                @if ($game->session_no == 1)
                                    <a href="{{route('game_unregister_waitlist', ['game' => $game])}}" type="button" class="btn btn-danger center-block" role="button">Abandonar lista de espera (¡CUIDADO!)</a>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>

                @if (($is_owner || $is_admin) && $game->players()->count())
                <div class="panel-body">
                    <h3>Jugadoras registradas</h3>

                    <ul>
                        @foreach ($game->players as $player)
                            <li>{{$player->name}} @if ($is_admin) <a href="{{route('game_unregister_user', ['game' => $game, 'user' => $player])}}">Eliminar</a> @endif </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (($is_owner || $is_admin)  && $game->waitlist()->count())
                <div class="panel-body">
                    <h3>Jugadoras en lista de espera</h3>

                    <ul>
                        @foreach ($game->waitlist as $player)
                            <li>{{$player->name}}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if ($game->canReadMessages($user))
                <div class="panel-body">
                    <h3>Mensajes</h3>

                    <p>Solo los participantes en la partida podrán ver este chat. Puedes usarlo para organizar la partida. Es un sistema muy limitado por lo que recomendamos usar otros servicios como Hangouts o Discord para comunicarte mas extensamente</p>

                    @if ($game->messages()->count())
                        @foreach($game->messages()->with('game')->get() as $message)
                            <blockquote class="blockquote">
                                <p>{{$message->content}}</p>

                                @if ($message->author->id === $game->owner->id)
                                    <footer class="blockquote-footer"><b>{{$message->author->name}}</b></footer>
                                @else
                                    <footer class="blockquote-footer">{{$message->author->name}}</footer>
                                @endif
                            </blockquote>
                        @endforeach
                    @endif

                    <br />

                    @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    {!! Form::open(['url' => route('message_create', ['game' => $game])]) !!}
                        <div class="form-group">
                            {!! Form::text('content', '', ['class' => 'form-control','placeholder'=>'Escribe hasta 500 caracteres']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Enviar mensaje', ['class' => 'btn btn-primary']) !!}
                        </div>
                    {!! Form::close() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
