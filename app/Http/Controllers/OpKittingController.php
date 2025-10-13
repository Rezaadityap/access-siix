<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpKittingController extends Controller
{
    public function index()
    {
        return view('op-kitting');
    }
}
