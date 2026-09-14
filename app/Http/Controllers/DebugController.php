<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DebugController extends Controller
{
    public function index(): View
    {
        return view('information');
    }
}
