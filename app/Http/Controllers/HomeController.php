<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      // Get the authenticated user's details
      $user = Auth::user();

      // Pass the user data to the view
      return view('home', ['user' => $user]);
    }

    public function test()
    {
      // Pass the user data to the view
      return view('test2');
    }
}
