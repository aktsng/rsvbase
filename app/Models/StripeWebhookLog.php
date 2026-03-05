<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookLog extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'payload',
        'status_code',
        'error_message',
        'processing_status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status_code' => 'integer',
        ];
    }

    // ─── Scopes ───────────────────────────────────

    public function scopeProcessed($query)
    {
        return $query->where('processing_status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('processing_status', 'failed');
    }

    // ─── Helpers ──────────────────────────────────

    /**
     * 冪等性チェック: 同一event_idが既に処理済みかどうか
     */
    public static function isAlreadyProcessed(string $eventId): bool
    {
        return static::where('event_id', $eventId)
            ->where('processing_status', 'processed')
            ->exists();
    }
}
