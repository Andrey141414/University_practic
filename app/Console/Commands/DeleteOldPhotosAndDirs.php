<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\userController;
use App\Models\postModel;

class DeleteOldPhotosAndDirs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        //postModel::where('title','Empty')->delete();

        //Storage::disk("local")->put('/IN_GOOD_HANDS/test1.txt',123);
        //print_r((new userController)->test1()); 
        //print_r(Storage::disk("public")->allDirectories());
        //return Command::SUCCESS;
    }
}
