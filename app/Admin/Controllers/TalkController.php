<?php

namespace App\Admin\Controllers;

use App\Talk;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use function foo\func;

class TalkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Charlas';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Talk());


        $grid->column('image_name', __('Image name'))->display(function ($image) {
            if (empty($image)) {
                return asset('img/sin_imagen.jpg');
            }
            return route('storage_get', $image);
        })->image();
        $grid->column('id', __('Id'));
        $grid->column('title', __('Título'));
        $grid->column('owner.name', __('Organizador'));
        $grid->column('starting_time', __('Hora de inicio'))->datetime('YYYY-MM-DD HH:mm:ss');;
        $grid->column('duration_hours', __('Duración'));
        $grid->column('stream_channel', __('Canal de emisión'));
        $grid->column('approved', __('Aprobada'))->bool()->filter([
            0 => 'No aprobadas',
            1 => 'Aprobadas'
        ]);
        $grid->column('created_at', __('Fecha de creación'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $users = User::query()->orderBy('name', 'asc')->pluck('name', 'id');
            $filter->equal('owner.id', 'Organizador')->select($users);
            $filter->like('title', 'Titulo');
        });
        $grid->export(function (Grid\Exporters\CsvExporter $exporter) {
            $exporter->filename('charlas');
            $exporter->except(['image_name']);
            $exporter->originalValue(['title', 'approved', 'starting_time']);
        });
        $grid->actions(function(Grid\Displayers\Actions $actions) {
            $actions->disableView();
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
        $show = new Show(Talk::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('image_name', __('Image name'));
        $show->field('starting_time', __('Starting time'));
        $show->field('duration_hours', __('Duration hours'));
        $show->field('stream_channel', __('Stream channel'));
        $show->field('approved', __('Approved'));
        $show->field('owner_id', __('Owner id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

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

        $form = new Form(new Talk());

        $form->disableViewCheck();

        $form->text('title', __('Titulo'));
        $form->select('owner_id', 'Organizador')->options(User::all()->pluck('name', 'id'));
        $form->textarea('description', __('Descripción'));
        $form->image('image_name', __('Imagen'))->move('public/images')->name(function ($name) {
            return "game_image" . uniqid() . "." . $name->extension();
        });
        $form->datetime('starting_time', __('Fecha de inicio'))->default(date('Y-m-d H:i:s'));
        $form->number('duration_hours', __('Duración'))->default(1);
        $form->text('stream_channel', __('Canal de emisión'));
        $form->switch('approved', __('Aprobada'))->states($states);

        return $form;
    }
}
