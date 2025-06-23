<?php

namespace App\Http\Controllers;

use App\Events\GameApproved;
use App\Events\PlayerRegistered;
use App\Events\PlayerUnregistered;
use App\Events\WaitlistPlayerRegistered;
use App\Game;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class GamesController extends Controller
{
    use ValidatesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Game::query()->orderBy('starting_time', 'asc');
        if (!isset($user) || !$user->isAdmin()) {
            $query->where('approved', true);
        }

        $filters = $request->get('filters', []);

        if (is_array($filters)) {
            if (array_key_exists('date', $filters) && $filters['date']) {
                $date = new Carbon($filters['date'], env('EVENT_TIMEZONE'));
                $startOfDate = (new Carbon($date))->startOfDay();
                $endOfDate = (new Carbon($date))->endOfDay();
                $query
                    ->where('starting_time', '>=', $startOfDate->toDateTimeString())
                    ->where('starting_time', '<', $endOfDate->toDateTimeString());
            } else {
                $filters['date'] = '';
            }
            if (array_key_exists('director', $filters) && $filters['director']) {
                $query->where('owner_id', $filters['director']);
            } else {
                $filters['director'] = [];
            }
            if (array_key_exists('beginner_friendly', $filters) && $filters['beginner_friendly']) {
                $filterBeginner = ($filters['beginner_friendly'] == "yes") ? true : false;
                $query->where('beginner_friendly', $filterBeginner);
            } else {
                $filters['beginner_friendly'] = false;
            }
            if (array_key_exists('approved', $filters) && $filters['approved']) {
                $filterApproved = ($filters['approved'] == "yes") ? true : false;
                $query->where('approved', $filterApproved);
            } else {
                $filters['approved'] = false;
            }
            if (array_key_exists('full', $filters) && $filters['full']) {
                if ($filters['full'] == 'yes') {
                    $query->whereRaw('maximum_players_number = signedup_players_number');
                } else {
                    $query->whereRaw('maximum_players_number > signedup_players_number')
                        ->where('session_no', 1);
                }
            } else {
                $filters['full'] = false;
            }
//            if (array_key_exists('safety_tools', $filters) && $filters['safety_tools']) {
//                $filterSafety = ($filters['safety_tools'] == "yes") ? true : false;
//                $query->where('safety_tools', $filterSafety);
//            } else {
//                $filters['safety_tools'] = false;
//            }
            if (array_key_exists('streamed', $filters) && $filters['streamed']) {
                $filterStreamed = ($filters['streamed'] == "yes") ? true : false;
                $query->where('streamed', $filterStreamed);
            } else {
                $filters['streamed'] = false;
            }
            if (array_key_exists('adult_content', $filters) && $filters['adult_content']) {
                $filterStreamed = ($filters['adult_content'] == "yes") ? true : false;
                $query->where('adult_content', $filterStreamed);
            } else {
                $filters['adult_content'] = false;
            }
        }


        $games = $query->paginate(9);

        $user_timezone = config('app.timezone');
        $masters = User::query()->orderBy('name', 'asc')->get();
        $filteredMasters = ["" => "-- Selecciona una directora --"];
        foreach ($masters as $master) {
            if ($master->games()->count()) {
                $filteredMasters[$master->id] = $master->name;
            }
        }

        // Gestión de favoritos
        $favs = [];
        if(isset($user)) {
            $favsResults = DB::table('game_player_fav')->select('game_id')->where('player_id', '=', $user->id)->get();
            if ($favsResults) {
                foreach ($favsResults as $fav) {
                    $favs[] = $fav->game_id;
                }    
            }
            
        } 

        return view('games.list', [
            'filters' => $filters,
            'user' => $user,
            'is_admin' => isset($user) && $user->isAdmin(),
            'games' => $games,
            'user_timezone' => $user_timezone,
            'game_masters' => $filteredMasters,
            'favs' => $favs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();
        if (!env('GAME_REGISTRATION_ENABLED', 'false') && 
            !$user->isTester() &&
            !$user->isAdmin()) {
            return redirect()->route('home');
        }

        return view('games.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $messages = [
            'required' => 'El campo :attribute es necesario.',
            'string' => 'El campo :attribute es necesario.',
            'max' => 'El campo :attribute no puede ser mas grande que :max caracteres.',
            'min' => 'El campo :attribute no puede ser menor que :min',
        ];

        $validationRules = [
            'title' => 'string|max:150|required',
            'description' => 'string|max:5000|required',
            'game_system' => 'string|max:250|required',
            'platform' => 'string|max:250|required',
            'starting_time' => 'string|max:250|date_format:d/m/Y H:i|required',
            'duration_hours' => 'integer|min:1|required',
            'maximum_players_number' => 'integer|min:1|required',
            'stream_channel' => 'string|max:250|nullable',
            'content_warning' => 'string|max:250|required',
            'safety_tools' => 'required',
        ];

        Validator::make($request->all(), $validationRules, $messages)->validate();

        if ($request->hasFile('game_image') && $request->file('game_image')->isValid()) {
            $file_name = 'game_image' . uniqid() . '.' . $request->game_image->extension();
            $image_path = $request->game_image->storeAs('public/images', $file_name);
        }

        $startingTime = Carbon::createFromFormat('d/m/Y H:i', $request->get('starting_time'), auth()->user()->timezone)->setTimezone(env('EVENT_TIMEZONE'));
        $eventStart = Carbon::createFromFormat('d/m/Y H:i', env('EVENT_START'), env('EVENT_TIMEZONE'));
        $eventEnd = Carbon::createFromFormat('d/m/Y H:i', env('EVENT_END'), env('EVENT_TIMEZONE'));

        if ($startingTime < $eventStart || $eventEnd < $startingTime) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'starting_time' => ['Debes introducir una hora de inicio entre 31/03/2021 08:00 GMT+2 y 04/04/2021 21:00 GMT+2'],
            ]);
            throw $error;
        }

        $edit = false;
        if ($gameId = $request->get('game_id')) {
            $edit = true;
            $game = Game::find($gameId);
        } else {
            $game = new Game();
            $game->owner_id = auth()->user()->id;
            $game->session_no = 1;
            //$game->sessions_number = $request->get('sessions_number');
            $game->sessions_number = 1;
        }


        $game->title = $request->get('title');
        $game->description = $request->get('description');
        $game->game_system = $request->get('game_system');
        $game->platform = $request->get('platform');
        $game->starting_time = $startingTime;
        $game->duration_hours = $request->get('duration_hours');
        $game->maximum_players_number = $request->get('maximum_players_number');
        $game->stream_channel = $request->get('stream_channel');
        $game->streamed = $request->has('streamed') && $request->get('streamed') === 'streamed' ? true : false;
        $game->beginner_friendly = $request->has('beginner_friendly') && $request->get('beginner_friendly') === 'beginner_friendly' ? true : false;
        $game->safety_tools = $request->get('safety_tools');
        $game->adult_content = $request->has('adult_content') && $request->get('adult_content') === 'adult_content' ? true : false;
        $game->content_warning = $request->get('content_warning');

        if (isset($image_path)) {
            $game->image_name = $file_name;
        }

        $game->save();

        if ($game->sessions_number > 1 && !$edit) {
            return redirect()->route('multiple_sessions_post', ['game' => $game]);
        }


        return redirect()->route('game_success');
    }

    public function success()
    {
        return view('games.success');
    }

    public function approve(Game $game)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return redirect()->route('game_view', ['game' => $game]);
        }
        $game->approved = true;
        $game->save();
        event(new GameApproved($game));
        return redirect()->route('game_view', ['game' => $game])->with('status', '¡Partida aprobada!');
    }

    public function approveStore(Game $game, Request $request)
    {
        $messages = [
            'required' => 'El campo :attribute es necesario.',
        ];

        $validationRules = [
            'day' => 'required',
        ];
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return redirect()->route('game_view', ['game' => $game]);
        }
        Validator::make($request->all(), $validationRules, $messages)->validate();
        $game->starting_time = $request->get('day') . " " . $request->get('hour') . ":" . $request->get('minute') . ":00";
        $game->approved = true;
        $game->save();
        event(new GameApproved($game));
        return redirect()->route('game_view', ['game' => $game])->with('status', '¡Partida aprobada!');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Game $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        $user = auth()->user();

        $is_owner = $game->isOwner($user);

        if (!$game->isApproved() && !$is_owner && (!isset($user) || !$user->isAdmin())) {
            return redirect()->route('home');
        }

        $user_timezone = config('app.timezone');

        $registration_open = env('GAME_SIGNUP_ENABLED', false);

        $is_partial = $game->maximum_players_number === 0;

        $is_full = $game->isFull() && !$is_partial;

        $is_registered = $game->isRegistered($user);

        $is_waitlisted = $game->isWaitlisted($user);

        return view('games.show', [
            'game' => $game,
            'user' => $user,
            'is_owner' => $is_owner,
            'is_admin' => isset($user) && $user->isAdmin(),
            'user_timezone' => $user_timezone,
            'registration_open' => $registration_open,
            'is_full' => $is_full,
            'is_registered' => $is_registered,
            'is_partial' => $is_partial,
            'is_waitlisted' => $is_waitlisted,
        ]);
    }

    public function edit(Game $game)
    {
        $user = auth()->user();

        $is_owner = $game->isOwner($user);

        if (($game->isApproved())) {
            if (!$user->isAdmin() && !$is_owner) {
                return redirect()->route('game_view', ['game' => $game]);
            }
        } else {
            if (!$user->isAdmin() && !$is_owner) {
                return redirect()->route('game_view', ['game' => $game]);
            }
        }

        $user_timezone = config('app.timezone');

        $registration_open = env('GAME_SIGNUP_ENABLED', false);

        $is_partial = $game->maximum_players_number === 0;

        $is_full = $game->isFull() && !$is_partial;

        $is_registered = $game->isRegistered($user);

        $is_waitlisted = $game->isWaitlisted($user);

        return view('games.edit', [
            'game' => $game,
            'user' => $user,
            'is_owner' => $is_owner,
            'is_admin' => $user->isAdmin(),
            'user_timezone' => $user_timezone,
            'registration_open' => $registration_open,
            'is_full' => $is_full,
            'is_registered' => $is_registered,
            'is_partial' => $is_partial,
            'is_waitlisted' => $is_waitlisted,
        ]);
    }

    public function showImage($filename)
    {
        $path = 'public/images/' . $filename;

        if (!Storage::exists($path)) {
            abort(404);
        }

        $rootPath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();

        return response()->file($rootPath . $path);

    }

    public function register(Game $game)
    {
        /** \App\User $user */
        $user = auth()->user();
        if (!env('GAME_SIGNUP_ENABLED', 'false') && !$user->isAdmin()) {
            return redirect()->route('game_view', ['game' => $game]);
        }

        if (!$game->canRegister($user)) {
            abort(403, 'No puedes registrarte en este juego');
        }

        $signup_threshold = Carbon::createFromFormat('d/m/Y H:i', env('SIGNUP_THRESHOLD'), env('EVENT_TIMEZONE'));
        $now = Carbon::now(env('EVENT_TIMEZOME'));
        if ($now < $signup_threshold) {
            $signedup_games = $user->signupGames()->where('games.session_no', 1)->count();
            if ($signedup_games >= env('SIGNUP_LIMIT')) {
                return redirect()->route('game_view', ['game' => $game])
                    ->with('status', 'Has alcanzado el límite de inscripciones. Podrás apuntarte a más partidas a partir de '. env('SIGNUP_THRESHOLD') .', GMT+2');
            }
        }
//
//        $last_game_signedup = strtotime($user->last_game_signedup);
//        $signup_threshold = 0.25 * $signedup_games;
//        if ($last_game_signedup != null) {
//            $elapsed_time = time() - $last_game_signedup;
//            if ($elapsed_time < $signup_threshold) {
//                return redirect()->route('game_view', ['game' => $game])
//                    ->with('status', 'Todavía no puedes apuntarte a una nueva partida. Espera unos instantes.');
//            }
//        }



        DB::transaction(function () use ($game, $user) {
            $user->last_game_signedup = Carbon::now();
            $user->save();
            $game->signedup_players_number = $game->signedup_players_number + 1;
            $game->save();
            $game->players()->attach($user->id);

            // Buscar segundas sesiones de esta partida
            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->players()->attach($user->id);
            }

        });
        if (env('GAME_SIGNUP_ENABLED', 'false')) {
            event(new PlayerRegistered($user, $game));
        }

        return redirect()->route('game_view', ['game' => $game]);
    }

    public function unregister(Game $game)
    {
        $user = auth()->user();

        return $this->_unregister($game, $user);
    }

    public function unregisterPlayer(Game $game, User $user)
    {
        $this->_unregister($game, $user);
    }

    private function _unregister(Game $game, User $user)
    {
        if (!$game->isRegistered($user)) {
            abort(403, 'No estas registrado en este juego');
        }

        $was_full = $game->isFull();

        DB::transaction(function () use ($game, $user, $was_full) {
            $game->players()->detach($user->id);
            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->players()->detach($user->id);
            }

            if ($was_full && $game->waitlist()->count()) {
                $this->popWaitlist($game);
            } else {
                $game->signedup_players_number = $game->signedup_players_number - 1;
                $game->save();
            }
        });
        if (env('GAME_SIGNUP_ENABLED', 'false')) {
            event(new PlayerUnregistered($game, $user));
        }

        return redirect()->route('game_view', ['game' => $game]);
    }

    public function registerToWaitlist(Game $game)
    {
        $user = auth()->user();

        if (!$game->canRegisterToWaitlist($user)) {
            abort(403, 'No puedes registrarte en la reserva de este juego');
        }

        DB::transaction(function () use ($game, $user) {
            $game->waitlist()->attach($user->id, ['waitlisted_at' => Carbon::now()]);

            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->waitlist()->attach($user->id, ['waitlisted_at' => Carbon::now()]);
            }
        });

        return redirect()->route('game_view', ['game' => $game]);
    }

    public function unregisterToWaitlist(Game $game)
    {
        $user = auth()->user();

        if (!$game->isWaitlisted($user)) {
            abort(403, 'No estas en la lista de espera de este juego');
        }

        DB::transaction(function () use ($game, $user) {
            $game->waitlist()->detach($user->id);

            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->waitlist()->detach($user->id);
            }
        });

        return redirect()->route('game_view', ['game' => $game]);
    }

    private function popWaitlist(Game $game)
    {
        $waitlisted = $game->waitlist()->orderBy('game_waitlist.id', 'asc')->first();

        if (!$waitlisted) {
            return;
        }

        $game->waitlist()->detach($waitlisted->id);
        $game->players()->attach($waitlisted->id);

        $sessions = Game::query()->where('parent_id', $game->id)->get();
        foreach ($sessions as $session) {
            $session->waitlist()->detach($waitlisted->id);
            $session->players()->attach($waitlisted->id);
        }

        event(new PlayerRegistered($waitlisted, $game));
        event(new WaitlistPlayerRegistered($waitlisted, $game));
    }

    /* Gestión de favoritos */
    public function fav (Game $game)
    {
        $user = auth()->user();
        /*if (env('GAME_FAV_ENABLED', 'false') && !$user->isAdmin()) {
            Log::debug(env('GAME_FAV_ENABLED', 'false'));
            return;
        }*/

        DB::transaction(function () use ($game, $user) {
            if($maxPriority = DB::table('game_player_fav')->select('priority')->where('player_id', '=', $user->id)->orderBy('priority', 'DESC')->first()) {
                $maxPriority = $maxPriority->priority;
            }
            DB::table('game_player_fav')->insert([
                'player_id' => $user->id,
                'game_id' => $game->id,
                'priority' => $maxPriority+1,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ]);
        });
        return;
    }

    public function unfav (Game $game)
    {
        $user = auth()->user();
        /*if (env('GAME_FAV_ENABLED', 'false') && !$user->isAdmin()) {
            return;
        }*/
        
        DB::transaction(function () use ($game, $user) {
            if($priority = DB::table('game_player_fav')->select('priority')->where('player_id', '=', $user->id)->where('game_id', "=", $game->id)->orderBy('priority', 'DESC')->first()){
                DB::table('game_player_fav')->where('player_id', "=", $user->id)->where('priority', ">",$priority->priority)->decrement('priority');
            }

            DB::table('game_player_fav')->where('player_id', "=", $user->id)->where('game_id', "=", $game->id)->delete();
        });
        return;
    }

    public function exchange (Game $game1, Game $game2)
    {
        $user = auth()->user();
        /*if (env('GAME_FAV_ENABLED', 'false') && !$user->isAdmin()) {
            return;
        }*/

        DB::transaction(function () use ($game1, $game2, $user) {
            $priority1 = DB::table('game_player_fav')->select('priority')->where('player_id', '=', $user->id)->where('game_id', '=', $game1->id)->orderBy('priority', 'DESC')->first()->priority;
            $priority2 = DB::table('game_player_fav')->select('priority')->where('player_id', '=', $user->id)->where('game_id', '=', $game2->id)->orderBy('priority', 'DESC')->first()->priority;
             DB::table('game_player_fav')->where('player_id', "=", $user->id)->where('game_id', "=", $game1->id)->update(['priority' => $priority2]);
             DB::table('game_player_fav')->where('player_id', "=", $user->id)->where('game_id', "=", $game2->id)->update(['priority' => $priority1]);
        });
        return;
    }

    /* HACK PARA SORTEO */
    public function sorteo()
    {
        //exit;
        //$semilla = $this->argument('semilla');
        $semilla = "91a22a8a933af95cb73db91ab2ea6398";
        $users = User::orderBy('id', 'ASC')->get();
        $colaSorteo = [];

        Log::debug('Construimos la cola con la semilla... '.$semilla);

        foreach ($users as $user) {
            //Generamos lista de partidas y la convertimos en array para poder ir eliminando sin problemas
            $partidas = DB::table('game_player_fav')->select(['player_id', 'game_id', 'priority'])->distinct()->where('player_id', '=', $user->id)->orderBy('priority', 'ASC')->get()->toArray();
            
            //Generamos semilla
            $hash = md5($user->id.$semilla);
            $colaSorteo[$hash] = [
                'player' => $user, 
                'pendientes' => $partidas
            ];
        }

        //Ordenación basada en los hashes
        Log::debug('Ordenamos la cola basada en los hashes...');
        ksort($colaSorteo);

        //Condición de terminación de las rondas, en cada ronda se inicializa a false y se modifica con cada asignación, de forma 
        //que seguimos iterando hasta que haya una ronda sin asignaciones.
        $sorteo = true; 
        $ronda = 1;

        //Arrancamamos el sorteo
        Log::debug('Arrancamos el sorteo...');
        //Fila 0
        $game = Game::find(62);
        $user = User:: find(148);
        $this->sregister($user, $game);
        /*try {*/
            while($sorteo) {
                $sorteo = false;
                Log::debug('RONDA '.$ronda);
                foreach ($colaSorteo as $hash => $userINFO) {
                    $asignado = false;
                    while(!$asignado) {
                        $user = $userINFO['player'];
                        $signedup_games = $user->signupGames()->where('games.session_no', 1)->count();
                        if ($signedup_games >= env('SIGNUP_LIMIT')) {
                            Log::debug('Jugador '.$user->id.' '.$user->name.' ha alcanzado el límite de partidas.');
                            $asignado = true;
                        } elseif($game_fav = array_shift($colaSorteo[$hash]['pendientes'])) {
                            $game = Game::find($game_fav->game_id);
                            if ($game !== null) {
                                if ($game->isPartial()) {
                                    $game = $game->parent()->first();
                                }
                                if ($game->canRegister($user)) {
                                    $this->sregister($user, $game);
                                    Log::debug('Registrado jugador '.$user->id.' '.$user->name.' a partida '.$game->id.' '.$game->title);
                                    //Hay movimiento en la cola, por tanto podemos seguir iterando
                                    $sorteo = true;
                                    $asignado = true;
                                } elseif ($game->isFull() && !$game->isRegistered($user) && !$game->isWaitlisted($user) ) {
                                    $this->swaitlist($user, $game);
                                    Log::debug('Encolado jugador '.$user->id.' '.$user->name.' en partida '.$game->id.' '.$game->title.'. Buscando otra partida');
                                } elseif ($user->isBusy($game)) {
                                    Log::debug('El jugador '.$user->id.' '.$user->name.' está ocupado en la franja de '.$game->id.' '.$game->title.'. Buscando otra partida');
                                } else {
                                    Log::error('Jugador '.$user->id.' '.$user->name.' intentando apuntar dos veces en partida '.$game->id.' '.$game->title);
                                    if (!$user) {
                                         Log::debug('Es nulo');
                                    }

                                    if (!$game->isApproved()) {
                                        Log::debug('Partida sin aprobar');
                                    }

                                    if ($game->isFull()) {
                                        Log::debug('Partida llena');
                                    }

                                    if ($game->isRegistered($user)) {
                                        Log::debug('Jugador ya registrado');
                                    }

                                    if ($game->isOwner($user)) {
                                        Log::debug('Jugador propietario, seguimos buscando');
                                    }

                                    /*if ($game->isPartial()) {
                                        Log::debug('Segunda sesión');
                                    }*/
                                    //exit;
                                }
                            } else {
                                //La partida ha sido borrada, vamos por el siguiente jugador
                                Log::debug('La partida '.$game_fav->game_id.' ha sido borrada. Seguimos buscando hueco para '.$user->id.' '.$user->name);
                            }
                        } else {
                            //El jugador ya no tiene partidas pendientes
                            Log::debug('Al jugador '.$user->id.' '.$user->name.' no le quedan partidas pendientes');
                            $asignado = true;
                        }
                    }
                }
                $ronda++;
                
                //En cada ronda invertimos el orden de cola
                if($ronda % 2 == 0) {
                    krsort($colaSorteo);
                } else {
                    ksort($colaSorteo);
                }
                Log::debug('Reordenando la cola antes de arrancar la siguiente ronda');
                Log::debug('FIN RONDA '.$ronda);
            }
        /*} catch (Exception $e){
            Log::error($e->getMessage);
            Log::error(print_r($user, 1));
            Log::error(print_r($game, 1));
        }*/
        Log::debug('Sorteo finalizado');
         return redirect()->route('home');
    }

    private function sregister($user, $game) {
        DB::transaction(function () use ($game, $user) {
            $user->last_game_signedup = Carbon::now();
            $user->save();
            $game->signedup_players_number = $game->signedup_players_number + 1;
            $game->save();
            $game->players()->attach($user->id);

            // Buscar segundas sesiones de esta partida
            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->players()->attach($user->id);
            }
        });

        //if (env('GAME_SIGNUP_ENABLED', 'false')) {
            event(new PlayerRegistered($user, $game));
        //}
    }

    private function swaitlist($user, $game) {
        DB::transaction(function () use ($game, $user) {
            $game->waitlist()->attach($user->id, ['waitlisted_at' => Carbon::now()]);

            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->waitlist()->attach($user->id, ['waitlisted_at' => Carbon::now()]);
            }
        });
    }
}
