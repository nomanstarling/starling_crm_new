<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ListingBulkEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $listings;
    public $recipent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $listings, $recipent)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->listings = $listings;
        $this->recipent = $recipent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('admin.emails.listings')
            ->with([
                'recipent' => null,
                'message' => null,
                'listings' => $this->listings,
            ]);
    }

}
