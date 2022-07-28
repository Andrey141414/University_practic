<?php

namespace App\Console;

use App\Models\accessTokens;
use App\Models\refreshTokens;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\postModel;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();>= now() - interval 2 hour
        $schedule->call(function () {
            accessTokens::whereRaw('expires_at < now()')->delete();
        })->daily();

        $schedule->call(function () {
            refreshTokens::whereRaw('expires_at < now()')->delete();
        })->daily();

        
        $schedule->call(function () {
            
        if (Storage::disk("local")->exists('public/IN_GOOD_HANDS/is_exist.txt')) {}
        
        else{

                $posts = (new postModel())::all();

                $paths = [];
                foreach($posts as  $key =>  $post)
                {
                    $paths[$key] = 'IN_GOOD_HANDS/'.$post->id_user.'/'.$post->id;
                }
                
                foreach($paths as $key=>$path)
                {
                    
                    Storage::disk("local")->makeDirectory('public/'.$path);
                    for($i = 0;$i < count(Storage::disk("google")->allFiles($path));$i++)
                    {
                        
                        $content = Storage::disk("google")->get($path.'/'.$i.'.jpeg');
                        Storage::disk("local")->put('public/'.$path.'/'.$i.'.jpeg',$content);
                        
                        
                    }
                };
                
                $content = Storage::disk("google")->get('IN_GOOD_HANDS/is_exist.txt');
                Storage::disk("local")->put('public/IN_GOOD_HANDS/is_exist.txt',$content);
            }
             
        })->everyMinute();

       

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

}
