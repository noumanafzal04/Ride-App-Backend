<?php

namespace App\Mail;

use App\Models\InspectionRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InspectionStatusMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public InspectionRequest $inspectionRequest,
        public string $heading,
        public string $body,
    ) {}

    public function build()
    {
        return $this
            ->subject('EZRide Car Inspection — ' . $this->heading)
            ->view('emails.inspection-status');
    }
}
