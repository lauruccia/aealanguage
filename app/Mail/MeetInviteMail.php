<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $payload
    ) {}

    public function build(): self
    {
        $subject = $this->payload['subject'] ?? 'Invito lezione (Google Meet)';

        return $this
            ->subject($subject)
            ->view('emails.meet-invite')
            ->with(['p' => $this->payload]);
    }
}
