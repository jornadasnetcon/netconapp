@extends('layouts.app')

@section('style')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
@endsection


@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h1>Partidas</h1>
                </div>

                <div class="panel-body">
                    @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif

                    <p style="text-align: center;">Si tienes cualquier duda o comentario ponte en <a href="{{ env('CONTACT_URL', '#') }}" target="_blank"> contacto con nosotr@s</a></p>
                    <p><small>NOTA: si estáis registradas en la página os saldrá el horario en el huso con el que os
                            hayáis registrado, sino saldrá en GMT+2</small></p>


                    <p><small>Puedes encontrar el listado completo de las partidas de este año <a href="https://bit.ly/ListadoNetcon" target="_blank">AQUÍ</a></small></p>

                    <div class="row">
                        <div class="col-12">
                            <h3>Filtros</h3>
                        </div>
                        {!! Form::open(['url' => route('game_list'), 'method' => 'put']) !!}
                        <div class="col-12 col-md-6 col-lg-4">
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
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                {!! Form::label('filters[director]', 'Directora', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[director]', $game_masters, $filters["director"], ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                {!! Form::label('filters[beginner_friendly]', 'Iniciación', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[beginner_friendly]', array(
                                "" => "-- Todas --",
                                "yes" => "Sí",
                                "no" => "No"
                                ), $filters["beginner_friendly"], ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        @if ($is_admin)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                {!! Form::label('filters[approved]', 'Aprobada', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[approved]', array(
                                "" => "-- Todas --",
                                "yes" => "Sí",
                                "no" => "No"
                                ), $filters["approved"], ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        @endif

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                {!! Form::label('filters[full]', 'Completa', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[full]', array(
                                "" => "-- Todas --",
                                "yes" => "Sí",
                                "no" => "No"
                                ), $filters["full"], ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-group">
                                {!! Form::label('filters[streamed]', 'Emitida', ['class' => 'control-label']) !!}
                                {!! Form::select('filters[streamed]', array(
                                "" => "-- Todas --",
                                "yes" => "Sí",
                                "no" => "No"
                                ), $filters["streamed"], ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        {!! Form::submit('Filtrar',['class' => 'btn btn-primary']) !!}
                    </div>
                </div>
                {!! Form::close() !!}

                <div class="row games">
                    @foreach($games as $game)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card" style="background-image:url({{route('storage_get', $game->image_name)}});">
                            <div class="card-body">
                                <a href="{{route('game_view', ['game' => $game])}}" class="d-block">
                                    <p class="mb-0 game_title">{{$game->title}}</p>
                                    <p>{{$game->game_system}}</p>
                                    <div class="meta_info">
                                        <p>
                                            <i class="fa-regular fa-face-smile"></i> {{ $game->owner->name}}
                                        </p>
                                        <p>
                                            <i class="fa-regular fa-calendar"></i> {{ $game->starting_time
                                                        ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                                        : null
                                                        }}
                                        </p>
                                        <p>
                                            @if ($game->isPartial())
                                            <i class="fa-solid fa-lock-open"></i> Parcial
                                            @else
                                            @if ($game->maximum_players_number <= $game->signedup_players_number)
                                                <i class="fa-solid fa-lock"></i> Completa
                                                @else
                                                <i class="fa-solid fa-lock-open"></i> Disponible
                                                @endif
                                                @endif
                                        </p>
                                    </div>
                                </a>
                                @guest
                                @else
                                <div class="mt-3 button-container" data-game="{{ $game->id }}">
                                    @if(env('GAME_FAV_ENABLED', 'false') || $user->isTester())
                                    @if (in_array($game->id, $favs))
                                    <button class="removeFav" data-game="{{ $game->id }}"><i class="fa-solid fa-heart"></i> Eliminar de favoritas</button>
                                    @else
                                    <button class="addFav" data-game="{{ $game->id }}"><i class="fa-regular fa-heart"></i> Añadir a favoritas</button>
                                    @endif
                                    @endif
                                </div>
                                @endguest
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{ $games->appends(request()->except('page'))->links() }}

            </div>
        </div>
    </div>
</div>
</div>
@endsection