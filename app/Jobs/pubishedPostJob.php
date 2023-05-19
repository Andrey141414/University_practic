<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\pubishedPostMail;
use App\Models\postModel;
use App\Models\User;

class pubishedPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected postModel $post;
    protected User $user;
    protected $rejectionReason; 
    public function __construct($user,$post,$rejectionReason = null)
    {
        //
        $this->post = $post;
        $this->user = $user;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        
        Mail::to($this->user)->send(new pubishedPostMail($this->post,$this->rejectionReason));
        
    }
}
