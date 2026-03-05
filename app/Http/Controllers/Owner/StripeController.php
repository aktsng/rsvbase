<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        // 実際の運用ではconfigからキーを取得します
        $this->stripe = new StripeClient(config('services.stripe.secret', 'sk_test_placeholder'));
    }

    /**
     * 設定画面の表示
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // 施設情報は表示用に取得するが、Stripeの状態はUserから取る
        $facility = $user->facilities()->first();

        return Inertia::render('Owner/Stripe/Setup', [
            'user' => [
                'stripe_account_id' => $user->stripe_account_id,
                'stripe_account_status' => $user->stripe_account_status,
            ],
            'facility' => $facility ? [
                'name' => $facility->name,
                'is_published' => $facility->is_published,
            ] : null
        ]);
    }

    /**
     * Stripe Connectのオンボーディングリンクを生成しリダイレクト
     */
    public function connect(Request $request)
    {
        $user = $request->user();

        try {
            // アカウントが未作成の場合は作成
            if (!$user->stripe_account_id) {
                $account = $this->stripe->accounts->create([
                    'type' => 'express',
                    'country' => 'JP',
                    'email' => $user->email,
                    'business_type' => 'company',
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                ]);

                $user->update([
                    'stripe_account_id' => $account->id,
                    'stripe_account_status' => 'pending',
                ]);
            }

            // オンボーディングリンクの作成
            $accountLink = $this->stripe->accountLinks->create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('owner.stripe.refresh'),
                'return_url' => route('owner.stripe.return'),
                'type' => 'account_onboarding',
            ]);

            return Inertia::location($accountLink->url);

        } catch (\Exception $e) {
            Log::error('Stripe Connect Error: ' . $e->getMessage());
            return back()->with('error', 'Stripeとの通信に失敗しました。');
        }
    }

    /**
     * 成功して戻ってきた時の処理
     */
    public function handleReturn(Request $request)
    {
        $user = $request->user();

        try {
            // アカウントの状態を確認
            $account = $this->stripe->accounts->retrieve($user->stripe_account_id);

            if ($account->details_submitted) {
                // charges_enabled の確認
                $status = $account->charges_enabled ? 'complete' : 'pending';

                $user->update(['stripe_account_status' => $status]);

                if ($status === 'complete') {
                    return redirect()->route('owner.stripe.index')->with('success', 'Stripe Connectの連携が完了しました。施設の公開が可能になりました。');
                } else {
                    return redirect()->route('owner.stripe.index')->with('success', '情報の入力が完了しましたが、Stripeの審査待ち状態です。');
                }
            } else {
                return redirect()->route('owner.stripe.index')->with('error', 'Stripe Connectの入力が完了していません。');
            }
        } catch (\Exception $e) {
            Log::error('Stripe Account Retrieval Error: ' . $e->getMessage());
            return redirect()->route('owner.stripe.index')->with('error', 'Stripeアカウントの確認に失敗しました。');
        }
    }

    /**
     * 途中で離脱した時の再開用処理
     */
    public function handleRefresh(Request $request)
    {
        return redirect()->route('owner.stripe.index')->with('error', 'Stripe Connectの設定が中断されました。再度ボタンから設定を再開してください。');
    }
}
