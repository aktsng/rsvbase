<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_id',
        'check_in_date',
        'check_out_date',
        'status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in_time',
        'number_of_guests',
        'number_of_adults',
        'number_of_child_a',
        'number_of_child_b',
        'guest_remarks',
        'booked_price_details',
        'booked_cleaning_fee',
        'total_amount',
        'platform_fee_rate',
        'platform_fee',
        'refunded_amount',
        'stripe_payment_intent_id',
        'stripe_refund_id',
        'reservation_code',
        'stripe_fee',
        'platform_fee_refund_amount',
        'owner_net_amount',
        'owner_memo',
        'payment_method',
        'booking_source',
        'transportation',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'booked_price_details' => 'array',
            'booked_cleaning_fee' => 'integer',
            'total_amount' => 'integer',
            'platform_fee_rate' => 'decimal:4',
            'platform_fee' => 'integer',
            'refunded_amount' => 'integer',
            'stripe_fee' => 'integer',
            'platform_fee_refund_amount' => 'integer',
            'owner_net_amount' => 'integer',
            'number_of_adults' => 'integer',
            'number_of_child_a' => 'integer',
            'number_of_child_b' => 'integer',
        ];
    }

    // ─── Boot ─────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            if (empty($reservation->uuid)) {
                $reservation->uuid = (string) Str::uuid();
            }
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = self::generateUniqueCode();
            }
        });
    }

    private static function generateUniqueCode(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        do {
            $code = 'R-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }
        } while (self::where('reservation_code', $code)->exists());

        return $code;
    }

    // ─── Relationships ────────────────────────────

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // ─── Scopes ───────────────────────────────────

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    /**
     * 指定日付範囲と重なる予約を取得
     */
    public function scopeOverlapping($query, $checkIn, $checkOut)
    {
        return $query->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn);
    }

    // ─── Helpers ──────────────────────────────────

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Stripe決済手数料（3.6%）の概算
     */
    public function estimatedStripeFee(): int
    {
        return (int) ceil($this->total_amount * 0.036);
    }

    /**
     * 返金プレビュー計算
     * A: 決済額 / B: Stripe手数料(返還不可) / C: システム利用料の戻り / 残額
     */
    public function refundPreview(int $refundAmount): array
    {
        $stripeFee = $this->estimatedStripeFee();
        $platformFeeReturn = (int) floor($refundAmount * $this->platform_fee_rate);

        return [
            'payment_amount' => $this->total_amount,
            'stripe_fee' => $stripeFee,
            'platform_fee_return' => $platformFeeReturn,
            'refund_amount' => $refundAmount,
            'owner_remaining' => $this->total_amount - $refundAmount - $stripeFee + $platformFeeReturn,
        ];
    }

    // ─── Route Binding ────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
