@extends('layouts.app')

@section('content')



    <div class="container">

        <div class="row">

            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading"><h1>Editar partida</h1></div>

                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        <p><a href="{{route('home')}}">Volver a mi perfil</a></p>

                        <div id="sube-partida" class="account-box">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                        <!-- form -->

                            {!! Form::open(['url' => route('game_store'), 'files' => true]) !!}

                            {!! Form::hidden('game_id', $game->id) !!}

                            <div class="form-group">
                                {!! Form::label('title', 'Título', ['class' => 'control-label']) !!}
                                <p><small id="title" class="form-text text-muted">Nombre de la partida</small></p>
                                {!! Form::text('title', $game->title ,['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('description', 'Descripción', ['class' => 'control-label']) !!}
                                <p><small id="description" class="form-text text-muted">Información para la partida que
                                        pueda interesar a los posibles participantes en la misma: argumento,
                                        ambientación, peculiaridades, etc. Si vas a utilizar un sistema distinto del
                                        original del juego aquí es un buen sitio para advertirlo.</small></p>
                                {!! Form::textarea('description',$game->description,['class' => 'form-control','placeholder'=>'Escribe hasta 5000 caracteres']) !!}

                            </div>
                            <div class="form-group">
                                {!! Form::label('game_system', 'Sistema de juego', ['class' => 'control-label']) !!}
                                {!! Form::text('game_system',$game->game_system,['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('platform', 'Plataforma de juego', ['class' => 'control-label']) !!}
                                <p><small id="platform" class="form-text text-muted">Informa a tus jugadoras sobre que
                                        plataforma usaréis para comunicaros durante la partida (Fantasy Grounds, Roll20,
                                        Hangout, Skype, Discord, Telegram, Radiotelegrafo de Hilos, Telepatia
                                        Arcana,...)</small></p>
                                {!! Form::text('platform',$game->platform,['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('game_image', 'Imagen de la partida', ['class' => 'control-label']) !!}
                                {!! Form::file('game_image', ['accept' => 'image/*']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('starting_time', 'Fecha y Hora de inicio', ['class' => 'control-label']) !!}
                                <small id="starting_time" class="form-text text-muted">
                                    Fecha y Hora de inicio para la partida. Asumiremos que la hora corresponde a la zona
                                    horaria que has configurado. Recuerda que las jornadas duran <?= getenv('EVENT_STRING') ?>.
                                    <!--Si la partida va a tener multiples sesiones, esta fecha es solo para la primera.-->
                                </small>
                                {!! Form::text('starting_time', $game->starting_time, ['class' => 'form-control timepicker ']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('duration_hours', 'Número de horas de duración', ['class' => 'control-label']) !!}
                                <p><small id="platform" class="form-text text-muted">Duración aproximada de la sesión en
                                        horas</small></p>
                                {!! Form::number('duration_hours', $game->duration_hours,['class' => 'form-control']) !!}
                            </div>
                                <!--<div class="form-group">
                                    {!! Form::label('sessions_number', 'Numero de sesiones', ['class' => 'control-label']) !!}
                                    <p><small id="sessions_number" class="form-text text-muted">Número de sesiones de la
                                            partida, en la mayoría de los casos sera 1</small></p>
                                    {!! Form::number('sessions_number', 1,['class' => 'form-control']) !!}
                                </div>-->

                            <div class="form-group">
                                {!! Form::label('maximum_players_number', 'Número máximo de jugadoras', ['class' => 'control-label']) !!}
                                {!! Form::number('maximum_players_number', $game->maximum_players_number,['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('streamed', '¿Se emitirá la partida?', ['class' => 'control-label']) !!}
                                Si {!! Form::checkbox('streamed', 'streamed', $game->streamed) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('beginner_friendly', '¿Partida de iniciación?', ['class' => 'control-label']) !!}
                                Si {!! Form::checkbox('beginner_friendly', 'beginner_friendly', $game->beginner_friendly) !!}
                            </div>

                                <div class="form-group">
                                    {!! Form::label('safety_tools', 'Usa Herramientas de Seguridad', ['class' => 'control-label']) !!}
                                    <small class="form-text text-muted">
                                        Por ejemplo: Lineas y Velos, Carta X, etc. <a
                                                href="https://netconplay.com/guia-de-buenas-practicas/herramientas-en-la-mesa/"
                                                target="_blank">Mas info</a>
                                    </small>
                                    {!! Form::select('safety_tools', array(
                                        "" => "-- Herramientas de seguridad --",
                                        "Tarjeta X" => "Tarjeta X",
                                        "Líneas y Velos" => "Líneas y Velos",
                                        "Script Change" => "Script Change",
                                        "Hojas de Consentimiento para jugadoras" => "Hojas de Consentimiento para jugadoras",
                                        "Técnica Luxton" => "Técnica Luxton",
                                        "Puertas abiertas" => "Puertas abiertas",
                                        "CATS" => "CATS",
                                        "Señales de apoyo" => "Señales de apoyo",
                                        "Flor de apoyo" => "Flor de apoyo",
                                        "Chequeo durante partida" => "Chequeo durante partida",
                                        "Otras" => "Otras",
                                    ), $game->safety_tools, ['class' => 'form-control']) !!}
                                </div>

                            <div class="form-group">
                                {!! Form::label('content_warning', 'Aviso de Contenido Sensible', ['class' => 'control-label']) !!}
                                <small class="form-text text-muted">
                                    Contenido o temas de la partida de los que creas necesario avisar a las jugadoras:
                                    violencia sexual, dismorfia corporal, etc.
                                </small>
                                {!! Form::text('content_warning',$game->content_warning,['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('stream_channel', 'Canal de emisión', ['class' => 'control-label']) !!}
                                <p><small id="stream_channel" class="form-text text-muted">Si va a ser emitida indicanos
                                        la url del canal de emisión</small></p>
                                {!! Form::text('stream_channel',$game->stream_channel,['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::submit('Guardar partida',['class' => 'btn btn-primary']) !!}
                            </div>

                            {!! Form::close() !!}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
