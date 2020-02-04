<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\HomeController;

class Diario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'practical:diario {--p=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cosecha y siembra por fecha, por defecto extrae la fecha actual';

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
        if ($this->option('p')) $this->homecontroller->Diario($this->option('p'));
        else $this->homecontroller->Diario();
    }
}
