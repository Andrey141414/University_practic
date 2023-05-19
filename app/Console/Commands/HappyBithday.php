<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BithdayMail;
use App\Models\User;

class HappyBithday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:hb';

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

        die();
        $users = User::all();
        foreach($users as $user)
        {
            Mail::to($user)->send(new BithdayMail());
        }
        return Command::SUCCESS;
    }
}
