<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function base() {
        return redirect()->route('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_timezone = config('app.timezone');
        return view('home', [
            'user' => auth()->user(),
            'user_timezone' => $user_timezone,
        ]);
    }

    public function userbar() {
        return view( 'userbar');
    }

    public function consent() {
        return view('consent');
    }

    public function consent_store() {
        $user = auth()->user();

        $user->age_consent = 1;
        $user->minor = 0;
        $user->save();

        return redirect('home');
    }
}
