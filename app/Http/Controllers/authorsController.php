<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\authors; //All database transactions should be on model

class authorsController extends Controller
{
    public function index()
    {
        $data = (new authors)->getAuthors();

        return response()->json(['error' => false, 'data' => $data]);
    }

    public function authorComics($authorId)
    {
        $data = (new authors)->getAuthorComics($authorId);

        return response()->json(['error' => false, 'data' => $data]);
    }
}
