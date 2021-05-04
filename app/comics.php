<?php

namespace App;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class comics extends Model
{
    public function getAll()
    {
        return DB::table('comics')->get();
    }
}
