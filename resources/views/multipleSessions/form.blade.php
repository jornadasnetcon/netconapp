@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading"><h1>Crear Partida de Multiples Sesiones</h1></div>
                <div class="panel-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {!! Form::open(['url' => route('multiple_sessions_store', ['game' => $game])]) !!}

                    <div class="form-group">
                        {!! Form::label('starting_time_1', 'Fecha y Hora de Inicio Partida 1', ['class' => 'control-label']) !!}
                        {!! Form::text('starting_time_1', $game->starting_time->setTimezone($user->timezone)->format('d/m/Y H:i'), ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                    </div>

                   <!-- @for ($i = 2; $i <= $game->sessions_number; $i++)

                        <div class="form-group">
                            {!! Form::label('starting_time_' . $i, 'Fecha y Hora de Inicio Partida ' . $i, ['class' => 'control-label']) !!}
                            {!! Form::text('starting_time_' . $i, null, ['class' => 'form-control timepicker ']) !!}
                        </div>

                    @endfor-->

                    <div class="form-group">
                        {!! Form::submit('Enviar partidas',['class' => 'btn btn-primary']) !!}
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
