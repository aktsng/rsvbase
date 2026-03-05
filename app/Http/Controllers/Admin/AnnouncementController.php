<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderByDesc('created_at')
            ->paginate(20)
            ->through(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'category_label' => $a->category_label,
                'category_color' => $a->category_color,
                'is_published' => $a->is_published,
                'is_pinned_dashboard' => $a->is_pinned_dashboard,
                'published_at' => $a->published_at?->format('Y/m/d H:i'),
                'created_at' => $a->created_at->format('Y/m/d H:i'),
            ]);

        return Inertia::render('Admin/Announcement/Index', [
            'announcements' => $announcements,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Announcement/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', 'string', 'in:info,maintenance,feature,important'],
            'is_published' => ['boolean'],
            'is_pinned_dashboard' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        // 公開フラグがtrueで公開日時が未設定の場合は現在日時を自動設定
        if (!empty($validated['is_published']) && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        // 非公開に戻す場合は published_at もクリア
        if (empty($validated['is_published'])) {
            $validated['is_published'] = false;
        }
        if (!isset($validated['is_pinned_dashboard'])) {
            $validated['is_pinned_dashboard'] = false;
        }

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'お知らせを作成しました。');
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Admin/Announcement/Edit', [
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'body' => $announcement->body,
                'category' => $announcement->category,
                'is_published' => $announcement->is_published,
                'is_pinned_dashboard' => $announcement->is_pinned_dashboard,
                'published_at' => $announcement->published_at?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', 'string', 'in:info,maintenance,feature,important'],
            'is_published' => ['boolean'],
            'is_pinned_dashboard' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        if (!empty($validated['is_published']) && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        if (empty($validated['is_published'])) {
            $validated['is_published'] = false;
        }
        if (!isset($validated['is_pinned_dashboard'])) {
            $validated['is_pinned_dashboard'] = false;
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'お知らせを更新しました。');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'お知らせを削除しました。');
    }
}
