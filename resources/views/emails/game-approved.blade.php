¡Hola {{$receiver->name}}!

<p>La partida {{$game->title}} se acaba de aprobar. Se te ha asignado el siguiente horario: {{$assignedTime}}</p>

<p>Puedes ver los datos de tu partida en: <a href="{{route('game_view', ['game' => $game], true)}}">{{$game->title}}</a></p>
