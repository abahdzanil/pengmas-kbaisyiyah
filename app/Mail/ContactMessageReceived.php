<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifies the school that someone submitted the Contact Us form.
 *
 * Goes to the address in site_settings (admin-editable under "Kelola
 * Contact"), with Reply-To set to the visitor so replying from the inbox
 * reaches them directly.
 */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        $recipient = SiteSetting::current()->email ?: config('mail.from.address');

        return new Envelope(
            to: [new Address($recipient)],
            replyTo: $this->contactMessage->email
                ? [new Address($this->contactMessage->email, $this->contactMessage->name)]
                : [],
            subject: 'Pesan Baru dari Website — '.$this->contactMessage->name,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.contact-message-received');
    }
}
