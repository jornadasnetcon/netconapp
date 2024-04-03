Hola {{$owner->name}}.

<p>Lamentamos decirte que uno de los jugadores ha abandonado tu partida con título <a href="{{route('game_view', ['game' => $game], true)}}">{{$game->title}}</a></p>

<p>No te preocupes, seguro que no tardarás nada de tiempo encontrar a alguien que lo sustituya. Y si necesitas ayuda, no dudes en <a href="{{ env('CONTACT_URL', '#') }}">ponerte en contacto con la organización</a>.</p>
