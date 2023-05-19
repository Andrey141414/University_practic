<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\postModel;
use Illuminate\Queue\SerializesModels;

class pubishedPostMail extends Mailable
{
    use Queueable, SerializesModels;

    protected postModel $post;
    protected $rejectionReason;
    public function __construct($post, $rejectionReason = null)
    {
        //
        $this->subject  = 'Ваш пост опубликован Сделки';
        $this->post = $post;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        return $this->view('Mail/PostPublished', [
            'name' => $this->post->title,
            'rejectionReason' => $this->rejectionReason,
        ]);


        //return $this->view('blog',[ "code" => $this -> code,'subject' => $this->subject ]);
    }
}
