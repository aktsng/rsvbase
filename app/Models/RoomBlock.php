<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'blocked_date',
    ];

    protected function casts(): array
    {
        return [
            'blocked_date' => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
