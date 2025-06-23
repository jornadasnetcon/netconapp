¡Hola {{$owner->name}}!

<p>Un nuevo jugador se ha registrado en tu partida de las {{ env('BRAND_NAME', 'NetCon') }} titulada: <b>{{$game->title}}</b></p>

<p>Su nombre es: <b>{{$player->name}}</b></p>

<p>Puedes verlo <a href="{{route('game_view', ['game' => $game], true)}}">aquí</a></p>





