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
                            <a href="{{route('talk_edit', ['game' => $talk], true)}}">Editar</a> |
                        @endif
                        @if (!$talk->isApproved() && $user->isAdmin())
                            <a href="{{route('talk_approve', ['game' => $talk], true)}}">Aprobar</a> |
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

                    <h3>{{$talk->title}}</h3>

                    <p style="padding: 15px; text-align: center;">
                    @if ($talk->image_name)
                        <img
                                class="image_game"
                                src="{{route('storage_get', $talk->image_name)}}"
                                alt="{{$talk->title}}"
                                style="max-width: 100%;"
                            >
                    @else
                        <img
                                class="image_game"
                                src="{{ asset('img/sin_imagen.jpg') }}"
                                alt="{{$talk->title}}"
                                style="max-width: 100%;"

                            >
                    @endif
                    </p>

                    <div class="col-md-8 col-md-offset-2">
                        <p><strong>Organizador</strong>: {{$talk->owner->name}}</p>
                        @if ($is_owner)
                            <p><strong>Estado</strong>: {{$talk->approved ? 'Aprobada' : 'Pendiente de aprobar'}}</p>
                        @endif
                        <p style="word-break: normal;">{!! nl2br(e($talk->description)) !!}</p>

                        <p><strong>Horario de inicio</strong>:
                            {{
                                $talk->starting_time
                                    ? $user_timezone
                                        ? (new Date($talk->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                        : (new Date($talk->starting_time->toDateTimeString()))->format('l j F Y H:i')
                                    : null
                            }}
                        </p>

                        <p><strong>Número de horas de duración</strong>: {{$talk->duration_hours}}</p>

                        <p><strong>Canal de emision</strong>: {{$talk->stream_channel}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
