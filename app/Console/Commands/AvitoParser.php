<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AvitoService;

class AvitoParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:avito';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle(AvitoService $avito)
    {
        $avito->start();
//        Cache::get('key');
    }
}
