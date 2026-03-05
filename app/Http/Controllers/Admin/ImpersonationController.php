<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * 代理ログイン開始
     */
    public function start(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', '管理者ユーザーへの代理ログインはできません。');
        }

        // 現在のAdminのIDをセッションに保存
        session()->put('impersonating_admin_id', Auth::id());
        Auth::login($user);

        return redirect()->route('owner.dashboard')
            ->with('success', "{$user->name} として代理ログイン中です。");
    }

    /**
     * 代理ログイン終了（Adminに戻る）
     */
    public function stop()
    {
        $adminId = session()->pull('impersonating_admin_id');

        if ($adminId) {
            $admin = User::findOrFail($adminId);
            Auth::login($admin);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', '管理者アカウントに戻りました。');
    }
}
