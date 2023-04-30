<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\reservation;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

class reservationCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected reservation $reservation;
    public function __construct($reservation)
    {
        //
        $this->subject  = 'Завершение Сделки';
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $post = postModel::find($this->reservation->id_post);
        return $this->view('Mail/reservationCompleted',[
            'name' => $post->title,
        ]);
        //return $this->view('blog',[ "code" => $this -> code,'subject' => $this->subject ]);
    }
}
