<?php

namespace App\Console\Commands;

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

        $reservations = reservation::where('expired_at', '<', Carbon::now())->get();
        foreach ($reservations as $res) {
            $res->delete();
        }
        return Command::SUCCESS;
    }
}
