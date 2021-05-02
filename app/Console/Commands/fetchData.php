<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class fetchData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marvel:fetch-data {--qty=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for migrating data from marvel api to database';
    protected $baseParams;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        /* setup base parameters for authentication */
        $this->baseParams = [
                                  'ts'        =>  date('Y-m-d H:i:s'), //timestamp
                                  'apikey'    =>  config('marvel.public_key'), //public key
                                  'hash'      =>  md5(date('Y-m-d H:i:s').config('marvel.private_key').config('marvel.public_key')), //md5 combination of ts, private key and public key
                            ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fetching Data....');

        $creatorUrl = config('marvel.url').'/v1/public/creators?';
        $creatorParams = $this->baseParams;
        $creatorParams['limit'] = $this->option('qty') ?? 10;

        $creators = $this->getCurl($creatorUrl, $creatorParams); //execute a get request using creator url and parameters

        $bar = $this->output->createProgressBar($creators->data->count);

        $bar->start();

        foreach($creators->data->results as $creator)
        {
            if($this->checkIfDataExists($creator->id, 'authors'))
            {
                $this->line("Skipping Creator ID: {$creator->id}");
                $bar->advance();
                continue;
            }

            /* Set Creator values to insert to table*/
            $creatorInsert = [
                                'id'             =>  $creator->id,
                                'first_name'     =>  $creator->firstName,
                                'last_name'      =>  $creator->lastName,
                                'thumbnail_url'  =>  "{$creator->thumbnail->path}.{$creator->thumbnail->extension}",
                                'created_at'     =>  \Carbon\Carbon::now(),
                                'updated_at'     =>  \Carbon\Carbon::now(),
                             ];

            /* Insert author data to table */
            $this->insertToTable($creatorInsert, 'authors');

            /* Check if there author has any available comics */
            if($creator->comics->available  > 0)
            {
                /* Fetch comics data from comics api */
                $comicUrl = "{$creator->comics->collectionURI}?";
                $comicParams = $this->baseParams;

                $comics = $this->getCurl($comicUrl, $comicParams); //execute a get request using comic url and parameters

                /* initialize comic array */
                $comicInsert = [];

                foreach($comics->data->results as $comic)
                {
                        if($this->checkIfDataExists($comic->id, 'comics'))
                        {
                            $this->line("Skipping Comic ID: {$comic->id}");
                            continue;
                        }

                        /* set comic array to insert to table */
                        $comicInsert[] = [
                                           'id'             =>  $comic->id,
                                           'title'          =>  $comic->title,
                                           'series_name'    =>  $comic->series->name,
                                           'description'    =>  $comic->description,
                                           'page_count'     =>  $comic->pageCount,
                                           'thumbnail_url'  =>  "{$comic->thumbnail->path}.{$comic->thumbnail->extension}",
                                           'created_at'     =>  \Carbon\Carbon::now(),
                                           'updated_at'     =>  \Carbon\Carbon::now(),
                                       ];

                }

                /* Convert data to collection for filtering */
                $comicIds = collect((object) $comicInsert);
                $comicIds = $comicIds->pluck('id'); //get ids to insert to pivot table

                /* Build Comic - Author data */
                $comicAuthor = [];
                foreach($comicIds as $cId)
                {
                    $comicAuthor[] = [
                                        'author_id' =>  $creator->id,
                                        'comic_id'  =>  $cId,
                                     ];
                }

                /* Insert comic data to table */
                $this->insertToTable($comicInsert, 'comics');

                if(!empty($comicAuthor))
                {
                    /* Insert author-comic data to table */
                    $this->insertToTable($comicAuthor, 'author_comics');
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info('Done!');
    }

    /* execute curl with get method */
    private function getCurl(string $url, array $params)
    {
        if(empty($url) || empty($params))
        {
            throw new Exception("Please check given URL and Parameters");
        }

        //adding get parameters to URL
        $url = $url.http_build_query($params);

        $curlOpt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url
        ];

        try
        {
            /* CURL setup and execution */
            $ch = curl_init();
            curl_setopt_array($ch, $curlOpt);
            $res = curl_exec($ch); //curl result
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $res = json_decode($res); //convert to json object

            /* check if result status is ok */
            if($statusCode != 200)
            {
                throw new Exception('CURL Error!');
            }

            return $res;
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }

    }

    private function insertToTable(array $data, string $table)
    {
        try
        {
            DB::beginTransaction();

            DB::table($table)
                ->insert($data);

            DB::commit();

            return ;
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            throw $ex;
        }
    }

    private function checkIfDataExists(int $id, string $table)
    {
        return DB::table($table)->where('id', $id)->exists();
    }
}
