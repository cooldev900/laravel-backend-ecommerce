<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $token)
    {
        $this->name = $name;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user['name'] = $this->name;
        $user['token'] = $this->token;

        return $this->from("noreply@omninext.app", "Omni Automotive")
            ->subject('Password Reset Link')
            ->view('reset-password', ['user' => $user]);
    }
}
