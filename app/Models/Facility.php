<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'name_en',
        'description',
        'description_en',
        'address',
        'address_en',
        'postal_code',
        'phone',
        'email',
        'booking_cutoff_hours',
        'check_in_start_time',
        'check_in_end_time',
        'check_out_time',
        'cancellation_policy_text',
        'terms_text',
        'platform_fee_rate',
        'is_published',
        'scta_business_name',
        'scta_representative',
        'scta_address',
        'scta_contact_email',
        'scta_contact_tel_disclaimer',
        'scta_price_description',
        'scta_payment_method_time',
        'scta_service_delivery_time',
        'slug',
        'image_path',
        'image_path_2',
        'image_path_3',
    ];

    protected $appends = ['image_url', 'image_url_2', 'image_url_3'];

    protected function casts(): array
    {
        return [
            'same_day_booking_cutoff_time' => 'string',
            'check_in_start_time' => 'string',
            'check_in_end_time' => 'string',
            'check_out_time' => 'string',
            'platform_fee_rate' => 'decimal:4',
            'is_published' => 'boolean',
            'booking_cutoff_hours' => 'integer',
        ];
    }

    // ─── Boot ─────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Facility $facility) {
            if (empty($facility->uuid)) {
                $facility->uuid = (string) Str::uuid();
            }
        });
    }

    // ─── Relationships ────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class)->orderBy('sort_order');
    }

    public function reservations()
    {
        return $this->hasManyThrough(Reservation::class, Room::class);
    }

    // ─── Route Binding ────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─── Accessors ────────────────────────────────

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? \Illuminate\Support\Facades\Storage::url($this->image_path)
            : null;
    }

    public function getImageUrl2Attribute(): ?string
    {
        return $this->image_path_2
            ? \Illuminate\Support\Facades\Storage::url($this->image_path_2)
            : null;
    }

    public function getImageUrl3Attribute(): ?string
    {
        return $this->image_path_3
            ? \Illuminate\Support\Facades\Storage::url($this->image_path_3)
            : null;
    }
}
