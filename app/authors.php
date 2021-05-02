<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class authors extends Model
{
    public function getAuthors()
    {
        return DB::table('authors')->get();
    }

    public function getAuthorComics($id)
    {
        /* Used where in rather than joining 3 tables, easier to read for database */

        $data = DB::table('comics')
                    ->whereRaw("id NOT IN (SELECT comic_id FROM author_comics WHERE author_id = $id)")
                    ->get();

        return $data;
    }
}
