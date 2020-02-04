<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\HomeController;

class SembrarWeb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'practical:sembrar';

    /**
     * The console insert data inportal web.
     *
     * @var string
     */
    protected $description = 'insert data inportal web';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $homecontroller;

    public function __construct(HomeController $HomeController)
    {
        parent::__construct();
        $this->homecontroller = $HomeController;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->homecontroller->sembrando();
    }
}
