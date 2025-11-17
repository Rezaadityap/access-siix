<?php

namespace App\Http\Controllers\FG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckSheetController extends Controller
{
    public function index()
    {
        return view('fg.checksheet');
    }
}
