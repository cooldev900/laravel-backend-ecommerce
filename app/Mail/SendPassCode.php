<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPassCode extends Mailable
{
    use Queueable, SerializesModels;

    public $passcode;
    public $id;
    public $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($passcode, $id, $name)
    {
        $this->passcode = $passcode;
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user['passcode'] = $this->passcode;
        $user['id'] = $this->id;
        $user['name'] = $this->name;

        return $this->from("noreply@omninext.app", "Omni Automotive")
            ->subject('One Time Passcode')
            ->view('passcode', ['user' => $user]);
    }
}
