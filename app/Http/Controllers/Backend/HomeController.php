<?php

namespace App\Http\Controllers\Backend;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Backend\BackendController;


class HomeController extends BackendController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('backend.home');
    }
}
