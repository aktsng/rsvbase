<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    /**
     * StripeからのWebhookを受け取り処理する
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret'); // .envのSTRIPE_WEBHOOK_SECRET

        $event = null;

        try {
            // シグネチャの検証（シークレットが設定されている場合のみ）
            if ($endpoint_secret) {
                $event = Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $endpoint_secret
                );
            } else {
                // シークレットがなく検証できない場合はそのままパース
                $event = json_decode($payload);
            }
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe Webhook Error: Invalid payload');
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe Webhook Error: Invalid signature');
            return response('Invalid signature', 400);
        }

        // イベントの処理
        $logEntry = \App\Models\StripeWebhookLog::create([
            'event_id' => $event->id,
            'event_type' => $event->type,
            'payload' => json_decode($payload, true),
            'status_code' => 200,
            'processing_status' => 'received',
        ]);

        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    Log::info('Stripe Webhook: PaymentIntent succeeded', ['id' => $paymentIntent->id]);
                    // ここで予約のステータス更新などが必要であれば行う
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    Log::warning('Stripe Webhook: PaymentIntent failed', ['id' => $paymentIntent->id]);
                    break;
                case 'payment_intent.created':
                case 'transfer.created':
                case 'application_fee.created':
                case 'charge.succeeded':
                case 'charge.updated':
                    // これらのイベントは正常系として受け流す
                    break;
                default:
                    Log::info('Stripe Webhook: Received unknown event type ' . $event->type);
            }

            $logEntry->update(['processing_status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Processing Error: ' . $e->getMessage());
            $logEntry->update([
                'processing_status' => 'failed',
                'error_message' => $e->getMessage(),
                'status_code' => 500
            ]);
        }

        return response('Webhook Handled', 200);
    }
}
