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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = config('marvel.url').'/v1/public/comics?';
        $params = (object) [
            'ts'        =>  date('Y-m-d H:i:s'), //timestamp
            'apikey'    =>  config('marvel.public_key'), //public key
            'hash'      =>  md5(date('Y-m-d H:i:s').config('marvel.private_key').config('marvel.public_key')), //md5 combination of ts, private key and public key
            'limit'     =>  $this->option('qty') ?? 10,
        ];

        //adding get parameters to URL
        $url = $url.http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);
        curl_close($ch);

    }
}
