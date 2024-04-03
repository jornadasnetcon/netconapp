@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><h1>Mi perfil</h1></div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p style="text-align: center;">
                    @if ($user->isTester() || env('GAME_REGISTRATION_ENABLED', false))
                        <a class="btn btn-primary" href="{{route('game_post')}}">¡SUBE UNA NUEVA PARTIDA!</a>
                    @endif
                    <a class="btn btn-primary" href="{{route('talk_post')}}">¡SUBE UNA NUEVA CHARLA!</a>
                    </p>

                    <p style="text-align: center;">
                    Si tienes cualquier duda o comentario ponte en <a href="{{ env('CONTACT_URL', '#') }}" target="_blank"> contacto con nosotras</a>
                    </p>

                    @if ($user->prices()->count())
                        <div class="alert alert-success" role="alert">
                                <h3>Premios Ganados</h3>
                                <p>
                                    Para reclamar tu premio, envíanos un correo a la direccion <b>info@netconplay.com</b> con el título <b>"NetCon Premio"</b>. En el contenido pon el <b>código</b> del premio y, si es físico, <b>la dirección</b> a la que deseas que se te envíe.
                                </p>
                                <table style="margin-top: 10px;" class="table table-condensed listado">
                                    <thead>
                                    <tr>
                                        <th>Premio</th>
                                        <th>Código</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($user->prices as $price)
                                        <tr>
                                            <td width="80%">
                                                {!! $price->description !!}
                                            </td>
                                            <td width="20%">
                                                {{$price->code}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            <p><strong>¡Date prisa! Tienes hasta el día 22 de noviembre de 2019 a las 23:59 para reclamar tu premio.</strong></p>
                            </div>
                    @endif

                    <h3>Partidas subidas: </h3>

                     @if ($user->games()->count())

                        <table class="table table-hover table-condensed listado">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Título</th>
                                    <th>Sistema de juego</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->games()->orderBy('starting_time', 'asc')->get() as $game)
                                    <tr>
                                         <td width="15%">
                                            <a href="{{route('game_view', $game->id)}}" title="Ver partida">
                                                @if ($game->image_name)
                                                <img
                                                        class="image_game"
                                                        src="{{route('storage_get', $game->image_name)}}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @else
                                                <img
                                                        class="image_game"
                                                        src="{{ asset('img/sin_imagen.jpg') }}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @endif
                                            </a>
                                        </td>
                                        <td width="25%">
                                            <a href="{{route('game_view', $game->id)}}" title="Ver partida" >{{$game->title}}</a>
                                        </td>
                                        <td width="20%">{{ $game->game_system }}</td>
                                        <td width="15%">
                                            @if ($game->starting_time)
                                                {{ $game->starting_time
                                                    ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                                    : null
                                                }}
                                            @else
                                                {{ $game->time_preference }} (provisional)
                                            @endif
                                        </td>
                                        <td width="10%">
                                            {{$game->approved ? 'Aprobada' : 'Pendiente'}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @endif
                    <h3>Charlas subidas: </h3>

                     @if ($user->talks()->count())

                        <table class="table table-hover table-condensed listado">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Título</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->talks()->orderBy('starting_time', 'asc')->get() as $game)
                                    <tr>
                                         <td width="15%">
                                            <a href="{{route('talk_view', $game->id)}}" title="Ver charla">
                                                @if ($game->image_name)
                                                <img
                                                        class="image_game"
                                                        src="{{route('storage_get', $game->image_name)}}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @else
                                                <img
                                                        class="image_game"
                                                        src="{{ asset('img/sin_imagen.jpg') }}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @endif
                                            </a>
                                        </td>
                                        <td width="25%">
                                            <a href="{{route('talk_view', $game->id)}}" title="Ver charla" >{{$game->title}}</a>
                                        </td>
                                        <td width="15%">
                                            @if ($game->starting_time)
                                                {{ $game->starting_time
                                                    ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                                    : null
                                                }}
                                            @else
                                                {{ $game->time_preference }} (provisional)
                                            @endif
                                        </td>
                                        <td width="10%">
                                            {{$game->approved ? 'Aprobada' : 'Pendiente'}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @endif

                   <h3>Partidas registradas: </h3>

                     @if ($user->signupGames()->count())

                        <table class="table table-hover table-condensed listado">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Titulo</th>
                                    <th>Sistema de juego</th>
                                    <th>Ambientación</th>
                                    <th>Horario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->signupGames()->orderBy('starting_time', 'asc')->get() as $game)
                                    <tr>
                                         <td width="15%">
                                            <a href="{{route('game_view', $game->id)}}" title="Ver partida">
                                                @if ($game->image_name)
                                                <img
                                                        class="image_game"
                                                        src="{{route('storage_get', $game->image_name)}}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @else
                                                <img
                                                        class="image_game"
                                                        src="{{ asset('img/sin_imagen.jpg') }}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @endif
                                            </a>
                                        </td>
                                        <td width="25%">
                                            <a href="{{route('game_view', $game->id)}}" title="Ver partida" >{{$game->title}}</a>
                                        </td>
                                        <td width="20%">
                                            @if ($game->game_system == "swdx")
                                                Savage Worlds Deluxe
                                            @endif
                                            @if ($game->game_system == "swade")
                                                Savage Worlds Aventura
                                            @endif
                                        </td>
                                        <td width="20%">
                                            {{$game->ambientation}}
                                        </td>
                                        <td width="20%">
                                            @if ($game->starting_time)
                                                {{ $game->starting_time
                                                    ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                                    : null
                                                }}
                                            @else
                                                {{ $game->time_preference }} (provisional)
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @endif

                    <h3>Partidas en lista de espera: </h3>

                     @if ($user->waitlistGames()->count())

                        <table class="table table-hover table-condensed listado">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Titulo</th>
                                    <th>Sistema de juego</th>
                                    <th>Ambientación</th>
                                    <th>Horario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->waitlistGames()->orderBy('starting_time', 'asc')->get() as $game)
                                    <tr>
                                         <td width="15%">
                                            <a href="{{route('game_view', $game->id)}}" title="Ver partida">
                                                @if ($game->image_name)
                                                <img
                                                        class="image_game"
                                                        src="{{route('storage_get', $game->image_name)}}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @else
                                                <img
                                                        class="image_game"
                                                        src="{{ asset('img/sin_imagen.jpg') }}"
                                                        alt="{{$game->title}}"
                                                        width="100%"
                                                    >
                                                @endif
                                            </a>
                                        </td>
                                        <td width="25%">
                                            <a href="{{route('game_view', $game->id)}}" title="Ver partida" >{{$game->title}}</a>
                                        </td>
                                        <td width="20%">
                                            @if ($game->game_system == "swdx")
                                                Savage Worlds Deluxe
                                            @endif
                                            @if ($game->game_system == "swade")
                                                Savage Worlds Aventura
                                            @endif
                                        </td>
                                        <td width="20%">
                                            {{$game->ambientation}}
                                        </td>
                                        <td width="20%">
                                            @if ($game->starting_time)
                                                {{ $game->starting_time
                                                    ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                                    : null
                                                }}
                                            @else
                                                {{ $game->time_preference }} (provisional)
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    @endif

                    <h3 class="d-block">Mis partidas favoritas:</h3><br/>
                    <p><b>Ordena tus partidas favoritas para tener más posibilidades de conseguir plaza.</b><br/>
                    El orden de tus actividades determina tu prioridad en el sorteo. Recuerda elegir cuidadosamente las más importantes y colocarlas en la parte superior.<br/>Durante el sorteo solo podrás optar a un máximo de 5 actividades y en caso de no conseguir plaza en tus favoritas, te incluiremos automáticamente en su lista de reserva.<br/>Tras el sorteo, podrás apuntarte a partidas que no estén completas hasta alcanzar ese máximo de 5 partidas. Más tarde eliminaremos esa limitación.</p>
                    <div class="row games">
                        @foreach($user->favGames()->orderBy('pivot_priority', 'asc')->get() as $game)
                            <div class="col-12" data-game="{{ $game->id }}">
                                <div class="card h-card" style="background-image:url({{route('storage_get', $game->image_name)}});"> 
                                    <div class="controllers d-md-none">
                                        <a class="moveUp" href="#" data-game="{{ $game->id }}"><i class="far fa-caret-square-up"></i> </a>
                                        <a class="moveDown" href="#" data-game="{{ $game->id }}"><i class="far fa-caret-square-down"></i> </a>
                                    </div>
                                    <div class="prioridad">{{ $game->pivot->priority }}</div>
                                <div class="card-body">  
                                    <a href="{{route('game_view', ['game' => $game])}}" class="d-block">
                                        <p class="mb-0 game_title">{{$game->title}}</p>
                                        <div class="meta_info">
                                            <p class="system">
                                               {{ $game->game_system }}
                                            </p>
                                            <p class="owner">
                                                <i class="fa-regular fa-face-smile"></i> {{ $game->owner->name }}
                                            </p>
                                            <p  class="date">
                                                <i class="fa-regular fa-calendar"></i> {{ $game->starting_time
                                                    ? (new Date($game->starting_time->setTimezone($user_timezone)->toDateTimeString()))->format('l j F Y H:i')
                                                    : null
                                                    }}
                                            </p>
                                        </div>

                                    </a>
                                    <div class="mt-3 button-container" data-game="{{ $game->id }}">
                                        @if(env('GAME_FAV_ENABLED', 'false'))
                                            <button class="removeFav" data-game="{{ $game->id }}" data-reload="reload"><i class="fa-solid fa-heart"></i> Eliminar de favoritas</button>
                                          @endif  
                                    </div>
                                </div>
                                @if(env('GAME_FAV_ENABLED', 'false'))
                                    <div class="controllers d-none d-md-flex">
                                        <a class="moveUp" href="#" data-game="{{ $game->id }}"><i class="far fa-caret-square-up"></i> </a>
                                        <a class="moveDown" href="#" data-game="{{ $game->id }}"><i class="far fa-caret-square-down"></i> </a>
                                    </div>
                                @endif
                                </div>
                            </div>
                        @endforeach    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
