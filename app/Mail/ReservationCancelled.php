<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancelled extends Mailable
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
        return new Envelope(
            subject: "【{$this->facility->name}】ご予約のキャンセル完了のお知らせ",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.cancelled',
            text: 'emails.reservations.cancelled_plain',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
