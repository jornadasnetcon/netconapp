@extends('layouts.app')

@section('content')



<div class="container">

    <div class="row">

        <div class="col-md-8 col-md-offset-2">

              <div class="panel panel-default">
                <div class="panel-heading"><h1>Aprobar partida</h1></div>

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

                        {!! Form::open(['url' => route('game_approve', ['game' => $game])]) !!}
                            {!! Form::hidden('game_id', $game->id) !!}
                        <p><strong>Preferencia del usuario:</strong> {{$game->time_preference}}</p>
                        <div class="form-group">
                                {!! Form::label('day', 'Día', ['class' => 'control-label']) !!}
                                {!! Form::select('day', [
                                    '' => '-- Selecciona un día --',
                                    '2019-10-11' => 'Viernes',
                                    '2019-10-12' => 'Sábado',
                                    '2019-10-13' => 'Domingo',
                                ], null ,['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-2">
                                    {!! Form::label('hour', 'Hora', ['class' => 'control-label']) !!}
                                    {!! Form::selectRange('hour', 0, 23, null, ['class' => 'form-control']) !!}
                                </div>
                                <div class="col-md-2">
                                    {!! Form::label('minute', 'Minutos', ['class' => 'control-label']) !!}
                                    {!! Form::select('minute', [
                                        "00" => "00",
                                        "15" => "15",
                                        "30" => "30",
                                        "45" => "45",
                                    ], null, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 2rem">
                                {!! Form::submit('Aprobar partida',['class' => 'btn btn-primary']) !!}
                        </div>

                        {!! Form::close() !!}

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
