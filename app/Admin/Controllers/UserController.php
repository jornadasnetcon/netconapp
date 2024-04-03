<?php

namespace App\Admin\Controllers;

use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use function foo\func;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Usuarios';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->disableCreateButton();

        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Nombre'))->sortable();
        $grid->column('email', __('Email'))->sortable();
        $grid->column('age_consent', __('Consentimiento de edad'))->bool()->sortable();
        $grid->column('timezone', __('Zona horaria'));
        $grid->column('ip', __('IP'));
        $grid->column('created_at', __('Fecha de registro'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('name', 'Nombre');
            $filter->like('email', 'Email');
        });
        $grid->export(function (Grid\Exporters\CsvExporter $exporter) {
            $exporter->filename('participantes');
            $exporter->originalValue(['age_consent']);
        });
   
        $grid->actions(function ($actions) {
            // append an action.
            $actions->prepend('<a href="mailto:'.$actions->row->email.'" target="_blank"><i class="fa fa-envelope"></i></a>');
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Nombre'));
        $show->field('email', __('Email'));
        $show->field('timezone', __('Zona horaria'));
        $show->field('about', __('Sobre la directora'));
        $show->field('created_at', __('Fecha de registro'));
        $show->field('minor', __('Es menor de edad'));
        $show->field('age_consent', __('Consentimiento de edad'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $timezone_list = \DateTimeZone::listIdentifiers();
        $timezone_list = array_combine($timezone_list, $timezone_list);
        $form = new Form(new User());

        $form->text('name', __('Nombre'));
        $form->email('email', __('Email'));
        $form->password('password', __('Cambiar contraseña'));
        $form->textarea('about', __('Sobre la directora'));
        $form->select('timezone', __('Zona horaria'))->options($timezone_list);
        $form->switch('age_consent', __('Consentimiento de edad'));
        $form->switch('minor', __('Es menor de edad'));

        $form->saving(function (Form $form) {
            $form->password = bcrypt($form->password);
        });

        return $form;
    }
}
