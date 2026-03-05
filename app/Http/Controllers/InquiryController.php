<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class InquiryController extends Controller
{
    /**
     * お問い合わせフォームの送信処理
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'facility_name' => 'nullable|string|max:255',
            'rooms_count' => 'nullable|string',
            'message' => 'required|string',
        ]);

        // 管理者メールアドレスへ送信 (config から取得)
        $adminEmail = config('mail.from.address');

        Mail::to($adminEmail)->send(new InquiryReceived($validated));

        return back()->with('success', 'お問い合わせを送信しました。');
    }
}
