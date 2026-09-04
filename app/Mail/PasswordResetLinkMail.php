<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $resetUrl)
    {
    }

    public function build(): self
    {
        return $this->subject('Reset your Rampart password')
            ->text('emails.password-reset-plain', ['resetUrl' => $this->resetUrl]);
    }
}
