¡Hola {{$user->name}}!

<p>Se ha realizado el sorteo de las NetCon y te ha correspondido un premio. ¡Enhorabuena!</p>

<p>Para saber qué premio te ha correspondido y cómo reclamarlo entra a <a href="{{route('home')}}">tu perfil</a> en la página de las NetCon.</p>

<p>¡Enhorabuena! Nos vemos en la próxima edición</p>

@if ($claim)
<p>¡Date prisa! Tienes hasta el día 22 de noviembre de 2019 a las 23:59 para reclamar tu premio. </p>
@endif
