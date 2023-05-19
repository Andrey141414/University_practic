<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\userController;
use App\Models\postModel;
use Illuminate\Support\Facades\Log;


/**
 * Runs the scheduler every 60 seconds as expected to be done by cron.
 */
class SchedulerDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:daemon ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call the scheduler every day.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $result = Storage::disk("local")->allDirectories('PHOTOS');
        
        //echo(substr_count($result[283],"/")); 
        
        // $str = explode ('/',$result[283]);
        // print_r($str);
        // if(isset($str[2]))
        // {
        //     echo('yes');
        // }
        // else
        // {
        //     echo('no');
        // }

        //echo($this->getStringBetween($result[250],"/","/"));
        //Storage::disk("local")->deleteDirectory('1321321');
        print_r($result);
    }

    function getStringBetween($str,$from,$to)
    {
        $sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
        return substr($sub,0,strpos($sub,$to));
    }
    function deletePostsWithoutFiles()
    {

        (new userController())->testinf();
        return;
        $posts = postModel::select('id','id_user')->limit(10)->get();
        
        
        foreach($posts as $post)
        {   

            $id = $post->id;
            $id_user = $post->id_user;
            $files = Storage::disk("local")->allFiles("PHOTOS/16/353");
            if(count($files) == 0)
            {
                $ans = Storage::disk("local")->deleteDirectory("PHOTOS/16/353");
                Log::debug($ans);
            }
            //print_r($files);
            return;
            //echo($post->id_user."\n");
        }
        

    }
}