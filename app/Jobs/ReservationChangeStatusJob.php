<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\reservationChangeStatusMail;
use App\Models\User;

class ReservationChangeStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected reservation $reservation;
    protected User $user;
    public function __construct($user,$reservation)
    {
        //
        $this->reservation = $reservation;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        
        Mail::to($this->user)->send(new reservationChangeStatusMail($this->reservation));
        
    }
}
