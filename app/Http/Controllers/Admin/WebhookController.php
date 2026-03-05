<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StripeWebhookLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebhookController extends Controller
{
    /**
     * Webhook Log ブラウザ表示
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');

        $query = StripeWebhookLog::orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('processing_status', $status);
        }

        if ($search) {
            $query->where('event_id', 'like', "%{$search}%")
                ->orWhere('event_type', 'like', "%{$search}%");
        }

        $logs = $query->paginate(20)->withQueryString()->through(fn($log) => [
            'id' => $log->id,
            'provider' => 'stripe',
            'event_id' => $log->event_id,
            'event_type' => $log->event_type,
            'status' => $log->processing_status,
            'created_at' => $log->created_at->format('Y/m/d H:i:s'),
        ]);

        return Inertia::render('Admin/Webhook/Index', [
            'logs' => $logs,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ]
        ]);
    }

    /**
     * ペイロード詳細表示API
     */
    public function show(string $id)
    {
        $log = StripeWebhookLog::findOrFail((int) $id);

        return response()->json([
            'id' => $log->id,
            'provider' => 'stripe',
            'event_id' => $log->event_id,
            'event_type' => $log->event_type,
            'status' => $log->processing_status,
            'payload' => $log->payload,
            'error_message' => $log->error_message,
            'created_at' => $log->created_at->format('Y/m/d H:i:s'),
        ]);
    }
}
