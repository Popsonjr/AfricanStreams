<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $fullname;
    public $email;
    public $subject;
    public $message;
    public $company;

    public function __construct(string $fullname, string $email, string $subject, string $message, string $company) {
        $this->fullname = $fullname;
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->company = $company;
    }

    public function build() {
        return $this->subject('New Contact Message: ' . $this->subject)
                ->view('emails.contact.admin');
    }
}