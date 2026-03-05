<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'category',
        'is_published',
        'is_pinned_dashboard',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_pinned_dashboard' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // ─── Scopes ───────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePinnedDashboard($query)
    {
        return $query->published()->where('is_pinned_dashboard', true);
    }

    // ─── Helpers ──────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'info' => 'お知らせ',
            'maintenance' => 'メンテナンス',
            'feature' => '新機能',
            'important' => '重要',
            default => 'お知らせ',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'info' => 'blue',
            'maintenance' => 'amber',
            'feature' => 'emerald',
            'important' => 'red',
            default => 'slate',
        };
    }
}
