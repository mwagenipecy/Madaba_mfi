<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public Loan $loan,
        public string $messageBody
    ) {
    }

    public function build()
    {
        $amount = number_format((float) ($this->loan->approved_amount ?? $this->loan->loan_amount ?? 0), 0);

        return $this->subject('Mkopo umeidhinishwa - '.$this->loan->loan_number)
            ->view('emails.loan-approved')
            ->with([
                'client' => $this->client,
                'loan' => $this->loan,
                'messageBody' => $this->messageBody,
                'amount' => $amount,
            ]);
    }
}
