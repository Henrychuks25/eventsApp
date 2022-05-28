<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagesController extends Controller
{
    public function __construct()
    {
        // language change
        $this->middleware('common');
    }

    public function view($page = null): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $page   = DB::table('pages')->where(['slug' => $page])->first();

        return view('eventmie::pages', compact('page'));
    }
}
