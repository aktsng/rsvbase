<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCompletedGuest extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $facility;
    public $room;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->room = $reservation->room;
        $this->facility = $this->room->facility;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->reservation->payment_method === 'onsite'
            ? "【{$this->facility->name}】ご予約を承りました（予約番号：{$this->reservation->reservation_code}）"
            : "【{$this->facility->name}】ご予約が完了しました（予約番号：{$this->reservation->reservation_code}）";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.guest_completed',
            text: 'emails.reservations.guest_completed_plain',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
