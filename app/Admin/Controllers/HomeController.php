<?php

namespace App\Admin\Controllers;

use App\Game;
use App\Http\Controllers\Controller;
use App\Talk;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('NetCon: Panel de administración');

            //$content->row(Dashboard::title());

            $content->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $usersGrid = new Grid(new User);
                    $usersGrid->header(function ($query) {
                        return "<h4>Últimos usuarios registrados</h4>";
                    });
                    $usersGrid->footer(function (Builder $query) {
                        $totalUsers = $query->count();
                        return "<p style='text-align: right'><strong>Total</strong>: ${totalUsers}</p>";
                    });
                    $usersGrid->model()->orderByDesc('id')
                        ->take(10);
                    $usersGrid->disableActions();
                    $usersGrid->disableTools();
                    $usersGrid->disableRowSelector();
                    $usersGrid->disablePagination();
                    $usersGrid->disableCreateButton();
                    $usersGrid->disableExport();
                    $usersGrid->disableFilter();
                    $usersGrid->column('id');
                    $usersGrid->column('name', 'Nombre');
                    $usersGrid->column('email');

                    $column->append($usersGrid->render());
                });

                $row->column(4, function (Column $column) {
                    $gamesGrid = new Grid(new Game);
                    $gamesGrid->header(function ($query) {
                        return "<h4>Últimas partidas registradas</h4>";
                    });
                    $gamesGrid->footer(function (Builder $query) {
                        $totalUsers = $query->count();
                        return "<p style='text-align: right'><strong>Total</strong>: ${totalUsers}</p>";
                    });
                    $gamesGrid->model()->orderByDesc('id')
                        ->take(10);
                    $gamesGrid->disableActions();
                    $gamesGrid->disableTools();
                    $gamesGrid->disableRowSelector();
                    $gamesGrid->disablePagination();
                    $gamesGrid->disableCreateButton();
                    $gamesGrid->disableExport();
                    $gamesGrid->disableFilter();
                    $gamesGrid->column('id');
                    $gamesGrid->column('title', 'Título');
                    $gamesGrid->column('owner.name', 'Director');

                    $column->append($gamesGrid->render());
                });

                $row->column(4, function (Column $column) {
                    $talksGrid = new Grid(new Talk());
                    $talksGrid->header(function ($query) {
                        return "<h4>Últimas charlas registradas</h4>";
                    });
                    $talksGrid->footer(function (Builder $query) {
                        $totalUsers = $query->count();
                        return "<p style='text-align: right'><strong>Total</strong>: ${totalUsers}</p>";
                    });
                    $talksGrid->model()->orderByDesc('id')
                        ->take(10);
                    $talksGrid->disableActions();
                    $talksGrid->disableTools();
                    $talksGrid->disableRowSelector();
                    $talksGrid->disablePagination();
                    $talksGrid->disableCreateButton();
                    $talksGrid->disableExport();
                    $talksGrid->disableFilter();
                    $talksGrid->column('id');
                    $talksGrid->column('title', 'Título');
                    $talksGrid->column('owner.name', 'Organizador');

                    $column->append($talksGrid->render());
                });


            });
        });
    }
}
