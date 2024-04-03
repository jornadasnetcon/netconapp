<p>¡Hola {{$player->name}}!</p>

<p>Tenemos muy buenas noticias. Estabas en la lista de espera de la partida <a href="{{route('game_view', ['game' => $game], true)}}">{{$game->title}}</a> en las NetCon. Ha quedado un sitio libre y se te ha registrado automáticamente en la partida. ¡Eso significa que podrás jugar en ella!</p>

<p>Te recomendamos que vayas a la página de la partida y dejes un mensaje allí confirmando que asistirás. Si no es asi, por favor, asegurate de <b>abandonar la partida</b> (pulsando el boton rojo de "Abandonar Partida" al final de la página) para que otro jugador pueda participar.</p>

<p>Puedes ver la partida <a href="{{route('game_view', ['game' => $game], true)}}">aquí</a></p>

<p>¡Disfruta!</p>