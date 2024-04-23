<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeadsAssignEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $leads;
    public $recipent;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $leads, $recipent, $user)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->leads = $leads;
        $this->recipent = $recipent;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('admin.emails.leads')
            ->with([
                'recipent' => null,
                'message' => $this->message,
                'leads' => $this->leads,
                'user' => $this->user
            ]);
    }

}
