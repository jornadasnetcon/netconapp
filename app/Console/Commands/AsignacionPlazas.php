<?php

namespace App\Console\Commands;

use App\User;
use App\Game;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AsignacionPlazas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asignacion:random {semilla}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reparte jugadores entre las partidas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $semilla = $this->argument('semilla');
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
                                    $this->register($user, $game);
                                    Log::debug('Registrado jugador '.$user->id.' '.$user->name.' a partida '.$game->id.' '.$game->title);
                                    //Hay movimiento en la cola, por tanto podemos seguir iterando
                                    $sorteo = true;
                                    $asignado = true;
                                } elseif ($game->isFull()) {
                                    $this->waitlist($user, $game);
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
    }

    private function register($user, $game) {
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
    }

    private function waitlist($user, $game) {
        DB::transaction(function () use ($game, $user) {
            $game->waitlist()->attach($user->id, ['waitlisted_at' => Carbon::now()]);

            $sessions = Game::query()->where('parent_id', $game->id)->get();
            foreach ($sessions as $session) {
                $session->waitlist()->attach($user->id, ['waitlisted_at' => Carbon::now()]);
            }
        });
    }

}
