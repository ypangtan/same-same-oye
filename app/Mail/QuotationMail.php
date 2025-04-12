<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Barryvdh\DomPDF\Facade\Pdf as PDF;

class QuotationMail extends Mailable
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
        $pdf = PDF::loadView('admin.mail.quotation', ['data' => $this->data])
        ->setPaper('a4', 'landscape');

        return $this->from('support@ifei.my', 'Infinite Design')
                ->with(['data' => $this->data])
                ->subject($this->getSubject())
                ->view('admin.mail.quotation_response')
                ->attachData($pdf->output(), 'document.pdf', [
                    'mime' => 'application/pdf',
                ]);
    }

    
    /**
     * Get the subject for the email.
     *
     * @return string
     */
    protected function getSubject()
    {
        switch ($this->data['action']) {
            case 'quotation':
                return 'Quotation';
            case 'invoice':
                return 'Invoice';
            case 'sales_order':
                return 'Sales Order';
            case 'delivery_order':
                return 'Delivery Order';
            default:
                return 'Quotation';
        }
    }
}
