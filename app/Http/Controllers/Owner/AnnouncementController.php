<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::published()
            ->orderByDesc('published_at')
            ->paginate(20)
            ->through(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'category' => $a->category,
                'category_label' => $a->category_label,
                'category_color' => $a->category_color,
                'published_at' => $a->published_at->format('Y/m/d'),
            ]);

        return Inertia::render('Owner/Announcement/Index', [
            'announcements' => $announcements,
        ]);
    }
}
