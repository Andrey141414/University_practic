<?php

namespace App\Console\Commands;

use App\Models\postModel;
use Illuminate\Console\Command;
use App\Models\reservation;
use Carbon\Carbon;

class DeleteOldReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:oldRes';

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

        $reservations = reservation::where('expired_at', '<', Carbon::now())->where('status','completed')->get();
        foreach ($reservations as $res) {
            $res->status = 'overdue';
            $res->save();
            $post = postModel::find($res->id_post);
            $post->status = 'active';
            $post->save();
        }
        return Command::SUCCESS;
    }
}
