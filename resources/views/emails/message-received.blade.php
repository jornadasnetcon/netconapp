¡Hola {{$receiver->name}}!

<p><b>{{$sender->name}}</b> ha enviado un mensaje en una de las partidas en la que participas en las env('BRAND_NAME', 'NetCon'): <a href="{{route('game_view', ['game' => $game], true)}}">{{$game->title}}</a></p>

<p>Puedes verlo <a href="{{route('game_view', ['game' => $game], true)}}">aquí</a></p>
