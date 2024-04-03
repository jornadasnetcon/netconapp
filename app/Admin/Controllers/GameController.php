<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Game\DeletePlaylist;
use App\Admin\Actions\Game\DeleteWaitlist;
use App\Admin\Actions\Game\Playlist;
use App\Admin\Actions\Game\Waitlist;
use App\Events\GameApproved;
use App\Game;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;
use function foo\func;

class GameController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Partidas';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Game());
        $grid->column('image_name', __('Image name'))->display(function ($image) {
            if (empty($image)) {
                return asset('img/sin_imagen.jpg');
            }
            return route('storage_get', $image);
        })->image();
        $grid->column('id', __('Id'));
        $grid->column('title', __('Título'));
        $grid->column('owner.name', __('Director'));
        $grid->column('game_system', __('Sistema de juego'));

        $grid->column('starting_time', __('Horario'))->datetime('YYYY-MM-DD HH:mm:ss');
        $grid->column('duration_hours', __('Duración'));
        $grid->column('maximum_players_number', __('Nº máximo de jugadores'));
        $grid->column('approved', __('Aprobada'))->bool()->filter([
            0 => 'No aprobadas',
            1 => 'Aprobadas'
        ]);
        $grid->column('streamed', __('Emitida'))->bool()->filter([
            0 => 'No emitidas',
            1 => 'Emitidas'
        ]);
        $grid->column('stream_channel', __('Canal de emisión'))->hide();
        $grid->column('created_at', __('Creación'))->hide();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $users = User::query()->orderBy('name', 'asc')->pluck('name', 'id');
            $filter->equal('owner.id', 'Director')->select($users);
            $filter->like('title', 'Titulo');
        });

        $grid->export(function (Grid\Exporters\CsvExporter $export) {
            $export->filename('partidas');
            $export->except(['image_name']);
            $export->originalValue(['title','starting_time', 'approved', 'streamed']);
        });

        $grid->actions(function ($actions) {
            // append an action.
            $actions->prepend('<a href="mailto:'.$actions->row->owner->email.'" target="_blank"><i class="fa fa-envelope"></i></a>');
        });


        return $grid;
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Game::findOrFail($id));

        $yesNoCallback = function($content) {
            if (!$content)
                return "No";
            return "Sí";
        };

        $show->field('image_name', __('Imagen'))->image('', 500);
        $show->field('title', __('Título'));
        $show->field('owner.name', __('Director'));
        $show->field('description', __('Descripción'));
        $show->field('game_system', __('Sistema de juego'));
        $show->field('platform', __('Plataforma de juego'));
        $show->field('starting_time', __('Horario'));
        $show->field('duration_hours', __('Duración'));
        $show->field('maximum_players_number', __('Número máximo de participantes'));
        $show->field('signedup_players_number', __('Participantes registrados'));
        $show->field('streamed', __('¿Se emitirá la partida?'))->as($yesNoCallback);
        $show->field('stream_channel', __('Canal de emisión'));
        $show->field('beginner_friendly', __('¿Partida de iniciación?'))->as($yesNoCallback);
        $show->field('approved', __('Aprobada'))->as($yesNoCallback);
        $show->field('safety_tools', __('Herramientas de seguridad'));
        $show->field('content_warning', __('Aviso de contenido sensible'));

        $show->messages('Mensajes en la partida', function (Grid $messagesGrid) use ($id){
            $messagesGrid->resource('/admin/user');
            $messagesGrid->disableExport();
            $messagesGrid->disableCreateButton();
            $messagesGrid->disableFilter();
            $messagesGrid->disablePagination();
            $messagesGrid->content();
            $messagesGrid->author()->name();


            $messagesGrid->setActionClass(Grid\Displayers\DropdownActions::class);

            $messagesGrid->actions(function (Grid\Displayers\DropdownActions $actions) use ($messagesGrid, $id){
                $actions->disableView();
                $actions->disableDelete();
                $actions->disableEdit();
                //$actions->add(new DeletePlaylist);
                //$actions->add(new Waitlist);
            });
        });

        $show->players('Jugadores registrados', function (Grid $playersGrid) use ($id){
            $playersGrid->resource('/admin/games');
            $playersGrid->disableExport();
            $playersGrid->disableCreateButton();
            $playersGrid->disableFilter();
            $playersGrid->disablePagination();
            $playersGrid->name();
            $playersGrid->email();


            $playersGrid->setActionClass(Grid\Displayers\DropdownActions::class);

            $playersGrid->actions(function (Grid\Displayers\DropdownActions $actions) use ($playersGrid, $id){
                $actions->disableView();
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->add(new DeletePlaylist);
                $actions->add(new Waitlist);
            });
        });
        $show->waitlist('Jugadores en lista de espera', function (Grid $playersGrid) use ($id){
            $playersGrid->resource('/admin/games');
            $playersGrid->disableExport();
            $playersGrid->disableCreateButton();
            $playersGrid->disableFilter();
            $playersGrid->disablePagination();
            $playersGrid->name();
            $playersGrid->email();


            $playersGrid->setActionClass(Grid\Displayers\DropdownActions::class);

            $playersGrid->actions(function (Grid\Displayers\DropdownActions $actions) use ($playersGrid, $id){
                $actions->disableView();
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->add(new DeleteWaitlist);
                $actions->add(new Playlist);
            });
        });
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $states = [
            'on'  => ['value' => 1, 'text' => 'SÍ', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'NO', 'color' => 'danger'],
        ];
        $form = new Form(new Game());

        $form->text('title', __('Título'));
        $form->select('owner_id', 'Director')->options(User::all()->pluck('name', 'id'));
        $form->textarea('description', __('Descripción'));
        $form->text('game_system', __('Sistema de juego'));
        $form->text('platform', __('Plataforma de juego'));
        $form->image('image_name', __('Imagen'))->move('public/images')->name(function ($name) {
            return "game_image" . uniqid() . "." . $name->extension();
        });
        $form->datetime('starting_time', __('Horario'))->default(date('Y-m-d H:i:s'));
        $form->number('duration_hours', __('Duración (horas)'));
        $form->number('maximum_players_number', __('Número máximo de participantes'));
        $form->switch('streamed', __('¿Se emitirá la partida?'))->states($states);
        $form->text('stream_channel', __('Canal de emisión'));
        $form->switch('beginner_friendly', __('Partida de iniciación'))->states($states);
        $form->select('safety_tools', __('Herramientas de seguridad'))->options([
            "" => "-- Herramientas de seguridad --",
            "Tarjeta X" => "Tarjeta X",
            "Líneas y Velos" => "Líneas y Velos",
            "Script Change" => "Script Change",
            "Hojas de Consentimiento para jugadoras" => "Hojas de Consentimiento para jugadoras",
            "Técnica Luxton" => "Técnica Luxton",
            "Puertas abiertas" => "Puertas abiertas",
            "CATS" => "CATS",
            "Señales de apoyo" => "Señales de apoyo",
            "Flor de apoyo" => "Flor de apoyo",
            "Chequeo durante partida" => "Chequeo durante partida",
            "Otras" => "Otras",
        ])->required();
        $form->text('content_warning', __('Aviso de contenido sensible'))->required();
        $form->switch('approved', __('Aprobada'))->states($states);

        $form->saving(function (Form $form) {
            $approved = $form->approved == "on";
            if ($approved && $form->model()->approved != $approved) {
                $game = Game::find($form->model()->id);
                if ($game) {
                    event(new GameApproved($game));
                }
            }
        });

        return $form;
    }
}
