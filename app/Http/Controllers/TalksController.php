<?php

namespace App\Http\Controllers;

use App\Events\GameApproved;
use App\Events\PlayerRegistered;
use App\Events\PlayerUnregistered;
use App\Events\TalkApproved;
use App\Events\WaitlistPlayerRegistered;
use App\Talk;
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
use Intervention\Image\Facades\Image;

class TalksController extends Controller
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

        $query = Talk::query()->orderBy('starting_time', 'asc');
        if (!isset($user) || !$user->isAdmin()) {
            $query->where('approved', true);
        }
        $filters = $request->get('filters', []);

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
        if (array_key_exists('approved', $filters) && $filters['approved']) {
            $filterApproved = ($filters['approved'] == "yes") ? true : false;
            $query->where('approved', $filterApproved);
        } else {
            $filters['approved'] = false;
        }

        $games = $query->paginate(10);

        $user_timezone = config('app.timezone');
        $masters = User::query()->orderBy('name', 'asc')->get();
        $filteredMasters = ["" => "-- Selecciona una organizadora --"];
        foreach ($masters as $master) {
            if ($master->talks()->count()) {
                $filteredMasters[$master->id] = $master->name;
            }
        }
        return view('talks.list', [
            'filters' => $filters,
            'user' => $user,
            'games' => $games,
            'user_timezone' => $user_timezone,
            'game_masters' => $filteredMasters,
            'is_admin' => isset($user) && $user->isAdmin(),
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
        //!env('GAME_REGISTRATION_ENABLED', 'false') &&
        /*if (!$user->isTester() &&
            !$user->isAdmin()) {
            return redirect()->route('home');
        }*/

        return view('talks.form');
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
            'starting_time' => 'string|max:250|date_format:d/m/Y H:i|required',
            'duration_hours' => 'integer|min:1|required',
            'stream_channel' => 'string|max:250|required',
        ];

        Validator::make($request->all(), $validationRules, $messages)->validate();

        if ($request->hasFile('talk_image') && $request->file('talk_image')->isValid()) {
            $file_name = 'talk_image' . uniqid() . '.' . $request->talk_image->extension();
            $image_path = $request->talk_image->storeAs('public/images', $file_name);
        }

        $startingTime = Carbon::createFromFormat('d/m/Y H:i', $request->get('starting_time'), auth()->user()->timezone)->setTimezone(env('EVENT_TIMEZONE'));
        $eventStart = Carbon::createFromFormat('d/m/Y H:i', env('EVENT_START'), env('EVENT_TIMEZONE'));
        $eventEnd = Carbon::createFromFormat('d/m/Y H:i', env('EVENT_END'), env('EVENT_TIMEZONE'));

        if ($startingTime < $eventStart || $eventEnd < $startingTime) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'starting_time' => ['Debes introducir una hora de inicio entre 26/09/2020 17:00 GMT+2 y 27/09/2020 05:00 GMT+2'],
            ]);
            throw $error;
        }

        $edit = false;
        if ($gameId = $request->get('game_id')) {
            $edit = true;
            $game = Talk::find($gameId);
        } else {
            $game = new Talk();
            $game->owner_id = auth()->user()->id;
        }


        $game->title = $request->get('title');
        $game->description = $request->get('description');
        $game->starting_time = $startingTime;
        $game->duration_hours = $request->get('duration_hours');
        $game->stream_channel = $request->get('stream_channel');

        if (isset($image_path)) {
            $game->image_name = $file_name;
        }

        $game->save();

        return redirect()->route('talk_success');
    }

    public function success()
    {
        return view('talks.success');
    }

    public function approve(Talk $game)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return redirect()->route('talk_view', ['game' => $game]);
        }
        $game->approved = true;
        $game->save();
        event(new TalkApproved($game));
        return redirect()->route('talk_view', ['game' => $game])->with('status', '¡Charla aprobada!');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Game $game
     * @return \Illuminate\Http\Response
     */
    public function show(Talk $game)
    {
        $user = auth()->user();

        $is_owner = $game->isOwner($user);

        if (!$game->isApproved() && !$is_owner && (!isset($user) || !$user->isAdmin())) {
            return redirect()->route('home');
        }

        $user_timezone = config('app.timezone');

        return view('talks.show', [
            'talk' => $game,
            'user' => $user,
            'is_owner' => $is_owner,
            'is_admin' => isset($user) && $user->isAdmin(),
            'user_timezone' => $user_timezone,
        ]);
    }

    public function edit(Talk $game)
    {
        $user = auth()->user();

        $is_owner = $game->isOwner($user);

        if (($game->isApproved())) {
            if (!$user->isAdmin()) {
                return redirect()->route('talk_view', ['game' => $game]);
            }
        } else {
            if (!$user->isAdmin() && !$is_owner) {
                return redirect()->route('talk_view', ['game' => $game]);
            }
        }

        $user_timezone = config('app.timezone');

        return view('talks.edit', [
            'game' => $game,
            'user' => $user,
            'is_owner' => $is_owner,
            'is_admin' => $user->isAdmin(),
            'user_timezone' => $user_timezone,
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


}
