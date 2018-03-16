<?php

namespace App\Http\Controllers;

use App\Game;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

        $query = Game::query()
            ->where('approved', true);

        if ($request->has('date')) {
            $query->whereDate('starting_time', new Carbon($request->get('date')));
        }

        $games = $query->paginate(10);

        return view('games.list', [
            'user' => $user,
            'games' => $games,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('games.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
            'time_preference' => 'string|max:250|required',
            'duration_hours' => 'integer|min:1|required',
            'sessions_number' => 'integer|min:1|required',
            'maximum_players_number' => 'integer|min:1|required',
            'stream_channel' => 'string:250|nullable',
        ];

        Validator::make($request->all(), $validationRules, $messages)->validate();

        if($request->hasFile('game_image') && $request->file('game_image')->isValid()) {
            $file_name = 'game_image' . uniqid() . '.' . $request->game_image->extension();
            $image_path = $request->game_image->storeAs('public/images', $file_name);
        }

        $game = new Game();

        $game->title = $request->get('title');
        $game->description = $request->get('description');
        $game->game_system = $request->get('game_system');
        $game->platform = $request->get('platform');
        $game->time_preference = $request->get('time_preference');
        $game->duration_hours = $request->get('duration_hours');
        $game->sessions_number = $request->get('sessions_number');
        $game->maximum_players_number = $request->get('maximum_players_number');
        $game->stream_channel = $request->get('stream_channel');
        $game->streamed = $request->has('streamed') && $request->get('streamed') === 'streamed' ? true : false;
        $game->beginner_friendly = $request->has('beginner_friendly') && $request->get('beginner_friendly') === 'beginner_friendly' ? true : false;
        $game->owner_id = auth()->user()->id;

        if (isset($image_path)) {
            $game->image_name = $file_name;
        }

        $game->save();

        return redirect()->route('game_success');
    }

    public function success() {
        return view('games.success');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        $user = auth()->user();

        $is_owner = $game->isOwner($user);

        $user_timezone = config('app.timezone');

        $registration_open = env('GAME_REGISTRATION_ENABLED', false);

        $is_full = $game->isFull();

        $is_registered = $game->isRegistered($user);

        return view('games.show', [
            'game'=> $game, 
            'user' => $user,
            'is_owner' => $is_owner,
            'user_timezone' => $user_timezone,
            'registration_open' => $registration_open,
            'is_full' => $is_full,
            'is_registered' => $is_registered,
        ]);
    }

    public function showImage($filename) {
        $path = 'public/images/' . $filename;

        if (!Storage::exists($path)) {
            abort(404);
        }

        $rootPath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();

        return response()->file($rootPath . $path);

    }

    public function register(Game $game) {
        $user = auth()->user();

        if (!$game->canRegister($user)) {
            abort(403, 'No puedes registrarte en este juego');
        }

        DB::transaction(function () use ($game, $user) {
            $game->signedup_players_number = $game->signedup_players_number + 1;
            $game->save();
            $game->players()->attach($user->id);
        });

        return redirect()->route('game_view', ['game' => $game]);
    }
}
