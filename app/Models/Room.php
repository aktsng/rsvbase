<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id',
        'name',
        'description',
        'capacity',
        'base_price_per_night',
        'weekend_price_per_night',
        'cleaning_fee',
        'sort_order',
        'is_active',
        'image1_path',
        'image2_path',
        'image3_path',
        // 料金タイプ・定員
        'pricing_type',
        'min_guests',
        // 子供A設定
        'child_a_label',
        'child_a_policy',
        'child_a_is_counted',
        // 子供B設定
        'child_b_label',
        'child_b_policy',
        'child_b_is_counted',
        // 追加料金（人数単価用）
        'add_child_a_fee',
        'add_child_b_fee',
    ];

    protected $appends = ['image_urls'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'base_price_per_night' => 'integer',
            'weekend_price_per_night' => 'integer',
            'cleaning_fee' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'min_guests' => 'integer',
            'child_a_is_counted' => 'boolean',
            'child_b_is_counted' => 'boolean',
            'add_child_a_fee' => 'integer',
            'add_child_b_fee' => 'integer',
        ];
    }

    // ─── Helpers ──────────────────────────────────

    public function isPersonPricing(): bool
    {
        return $this->pricing_type === 'person';
    }

    // ─── Boot ─────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Room $room) {
            if (empty($room->uuid)) {
                $room->uuid = (string) Str::uuid();
            }
        });
    }

    // ─── Relationships ────────────────────────────

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function specialPrices()
    {
        return $this->hasMany(SpecialPrice::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function roomBlocks()
    {
        return $this->hasMany(RoomBlock::class);
    }

    public function blocks()
    {
        return $this->hasMany(RoomBlock::class);
    }

    // ─── Route Binding ────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─── Accessors ────────────────────────────────

    public function getImageUrlsAttribute(): array
    {
        $urls = [];
        for ($i = 1; $i <= 3; $i++) {
            $path = $this->{"image{$i}_path"};
            if ($path) {
                $urls[] = \Illuminate\Support\Facades\Storage::url($path);
            }
        }
        return $urls;
    }
}
