<?php

namespace App\mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $subjectLine,
        public string $messageBody
    ) {}

    public function build(): self
    {
        return $this
            ->subject('[Contact] '.$this->subjectLine)
            ->replyTo($this->userEmail, $this->userName)
            ->view('emails.contact')
            ->with([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'body' => $this->messageBody,
                'subjectLine' => $this->subjectLine,
            ]);
    }
}
