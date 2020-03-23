<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\HomeController;

class CosechaDspace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'practical:cosecha {--p=}';

    /**
     * The console retriv data dspace.
     *
     * @var string
     */
    protected $description = 'retriv data dspace';

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
        if ($this->option('p')) $this->homecontroller->cosecha($this->option('p'),1);
    }
}
