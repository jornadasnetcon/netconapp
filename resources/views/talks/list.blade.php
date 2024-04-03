@extends('layouts.app')

@section('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
@endsection


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading"><h1>Charlas</h1></div>

                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                            <p style="text-align: center;">Si tienes cualquier duda o comentario ponte en <a
                                        href="{{ env('CONTACT_URL', '#') }}" target="_blank"> contacto con nosotras</a></p>
                            <p><small>NOTA: si estáis registradas en la página os saldrá el horario en el huso con el que os
                                    hayáis registrado, sino saldrá en GMT+2</small></p>

                        <div>
                            <h3>Filtros</h3>
                            {!! Form::open(['url' => route('talk_list'), 'method' => 'put']) !!}
                            <div class="form-group">
                                {!! Form::label('filters[date]', 'Fecha', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[date]', array(
                                    "" => "-- Selecciona un día --",
                                    '2024-03-27' => 'Miércoles',
                                    '2024-03-28' => 'Jueves',
                                    '2024-03-29' => 'Viernes',
                                    '2024-03-30' => 'Sábado',
                                    '2024-03-31' => 'Domingo'
                                ), $filters["date"], ['class' => 'form-control']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('filters[director]', 'Organizadora', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[director]', $game_masters, $filters["director"], ['class' => 'form-control']) !!}
                            </div>
                            @if ($is_admin)
                                <div class="form-group">
                                    {!! Form::label('filters[approved]', 'Aprobada', ['class' => 'control-label']) !!}
                                    {!! Form::select('filters[approved]', array(
                                        "" => "-- Todas --",
                                        "yes" => "Sí",
                                        "no" => "No"
                                    ), $filters["approved"], ['class' => 'form-control']) !!}
                                </div>
                            @endif

                            <div class="form-group" style="margin-top: 1rem;">
                                {!! Form::submit('Filtrar',['class' => 'btn btn-primary']) !!}
                            </div>
                            {!! Form::close() !!}
                        </div>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Título</th>
                                <th style="text-align: right">Hora de inicio</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <tr>
                                    <td width="70%">
                                        <a href="{{route('talk_view', ['game' => $game])}}">{{$game->title}}</a>
                                    </td>
                                    <td style="text-align: right">
                                        {{ $game->starting_time
                                            ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                            : null
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $games->appends(request()->except('page'))->links() }}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
