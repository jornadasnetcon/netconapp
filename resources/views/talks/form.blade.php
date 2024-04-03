@extends('layouts.app')

@section('content')

<div class="container">

    <div class="row">

        <div class="col-md-8 col-md-offset-2">

            <div class="panel panel-default">
                <div class="panel-heading"><h1>Subir charla</h1></div>

                <div class="panel-body">

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

                        {!! Form::open(['url' => route('talk_store'), 'files' => true]) !!}

                        <div class="form-group">
                            {!! Form::label('title', 'Título', ['class' => 'control-label']) !!}
                            <p><small id="title" class="form-text text-muted">Título de la charla</small></p>
                            {!! Form::text('title','',['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('description', 'Descripción', ['class' => 'control-label']) !!}
                            <p><small id="description" class="form-text text-muted">Información y descripción de la actividad (contenidos, ponentes, etc)</small></p>
                            {!! Form::textarea('description','',['class' => 'form-control','placeholder'=>'Escribe hasta 5000 caracteres']) !!}

                        </div>

                        <div class="form-group">
                            {!! Form::label('talk_image', 'Portada', ['class' => 'control-label']) !!}
                            {!! Form::file('talk_image', ['accept' => 'image/*']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('starting_time', 'Fecha y Hora de inicio', ['class' => 'control-label']) !!}
                            <small id="starting_time" class="form-text text-muted">
                                Fecha y hora de inicio. Asumiremos que la hora corresponde a la zona
                                horaria que has configurado. Recuerda que las jornadas duran <?= getenv('EVENT_STRING') ?>.
                            </small>
                            {!! Form::text('starting_time', getenv('EVENT_START'), ['class' => 'form-control timepicker ']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('duration_hours', 'Número de horas de duración', ['class' => 'control-label']) !!}
                            <p><small id="platform" class="form-text text-muted">Duración aproximada de la charla en
                                    horas</small></p>
                            {!! Form::number('duration_hours', 1,['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('stream_channel', 'Canal de emisión', ['class' => 'control-label']) !!}
                            <p><small id="stream_channel" class="form-text text-muted">Desde dónde podrá ver la gente la charla</small></p>
                            {!! Form::text('stream_channel','',['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit('Enviar charla',['class' => 'btn btn-primary']) !!}
                        </div>

                        {!! Form::close() !!}

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
