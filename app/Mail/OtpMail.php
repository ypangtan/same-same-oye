<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // from
        return $this->from('support@sso.my', 'SSO')
        ->subject($this->getSubject())
        ->view('admin/mail/otp')
        ->with(['data' => $this->data]);
    }

    
    /**
     * Get the subject for the email.
     *
     * @return string
     */
    protected function getSubject()
    {
        switch ($this->data['action']) {
            case 'forgot_password':
                return 'Request Password Reset';
            default:
                return 'Register';
        }
    }
}
