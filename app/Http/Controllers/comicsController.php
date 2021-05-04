<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\comics;

class comicsController extends Controller
{
    public function index()
    {
        $data = (new comics)->getAll();

        return response()->json(['error' => false, 'data' => $data]);
    }
}
