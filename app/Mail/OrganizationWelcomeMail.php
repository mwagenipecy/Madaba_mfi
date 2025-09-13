<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrganizationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $organization;
    public $user;

    public function __construct(Organization $organization, User $user)
    {
        $this->organization = $organization;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Welcome to MicroFin Pro - Registration Successful')
                   ->markdown('emails.organization-welcome')
                   ->with([
                       'organization' => $this->organization,
                       'user' => $this->user,
                       'loginUrl' => route('login'),
                   ]);
    }
}
