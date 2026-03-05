<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function index()
    {
        return Inertia::render('Owner/Onboarding/Index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'accept_terms' => ['required', 'accepted'],
        ];

        // 初回ログイン（パスワード変更が必要）な場合のみバリデーション
        if ($user->needs_password_change) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $request->validate($rules);

        // データの更新
        if ($user->needs_password_change) {
            $user->password = Hash::make($request->password);
            $user->needs_password_change = false;
        }

        if (is_null($user->terms_accepted_at)) {
            $user->terms_accepted_at = now();
        }

        $user->save();

        return redirect()->route('owner.dashboard')
            ->with('success', 'オンボーディングが完了しました。RsvBaseへようこそ！');
    }
}
