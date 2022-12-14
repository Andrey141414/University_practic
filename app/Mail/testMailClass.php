<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class testMailClass extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public int $code;
    //public $content = 'Код подтверждения';
    public function __construct(int $code)
    {
        //
        $this->subject  = 'Код подтверждения';
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('blog',[ "code" => $this -> code,'subject' => $this->subject ]);
    }
}
