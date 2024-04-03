<?php

namespace App\Admin\Actions\Game;

use App\Game;
use App\GameWaitlist;
use Carbon\Carbon;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Playlist extends RowAction
{
    public $name = 'Pasar a lista de juego';

    public function handle(Model $model)
    {
        DB::transaction(function () use ($model) {
            $game = Game::find($model->game_id);
            $game->waitlist()->detach($model->waitlist_id);
            $game->signedup_players_number = $game->signedup_players_number + 1;
            $game->players()->attach($model->waitlist_id);
            $game->save();
        });

        return $this->response()->success('Operación realizada')->refresh();
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function retrieveModel(Request $request)
    {
        if (!$key = $request->get('_key')) {
            return false;
        }

        $modelClass = str_replace('_', '\\', $request->get('_model'));

        if ($this->modelUseSoftDeletes($modelClass)) {
            return $modelClass::withTrashed()->findOrFail($key);
        }

        return GameWaitlist::findOrFail($key);
    }
}