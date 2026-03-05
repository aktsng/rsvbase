<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * オーナー一覧の表示
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $query = User::where('role', 'owner')
            ->withCount('facilities')
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString()->through(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'facilities_count' => $u->facilities_count,
            'created_at' => $u->created_at->format('Y/m/d'),
        ]);

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * 新規登録フォーム
     */
    public function create()
    {
        return Inertia::render('Admin/User/Create');
    }

    /**
     * オーナーの保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
            'needs_password_change' => true, // 初回ログイン時に変更を強制
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'オーナーを登録しました。ログイン情報をオーナーへ伝えてください。');
    }

    /**
     * 編集画面
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/User/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * 更新
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'オーナー情報を更新しました。');
    }

    /**
     * 削除
     */
    public function destroy(User $user)
    {
        // 施設が紐付いている場合は削除不可（要件に応じて要検討）
        if ($user->facilities()->exists()) {
            return back()->with('error', 'このオーナーは施設を管理しているため削除できません。先に施設の紐付けを解除するか、施設を削除してください。');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'オーナーを削除しました。');
    }
}
