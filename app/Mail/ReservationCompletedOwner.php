<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCompletedOwner extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $facility;
    public $room;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->room = $reservation->room;
        $this->facility = $this->room->facility;
    }

    public function envelope(): Envelope
    {
        $subject = $this->reservation->payment_method === 'onsite'
            ? "【手動予約】{$this->facility->name} に新しい予約を登録しました"
            : "【新規予約】{$this->facility->name} に新しい予約が入りました";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.owner_completed',
            text: 'emails.reservations.owner_completed_plain',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
