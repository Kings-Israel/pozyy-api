<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $email, string $code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.reset-password')->with([
            'content' => 'Please enter the following code to reset your password.',
            'code' => $this->code,
        ]);
    }
}
