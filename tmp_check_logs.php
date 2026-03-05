<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Log Count: " . \App\Models\StripeWebhookLog::count() . "\n";
$latest = \App\Models\StripeWebhookLog::latest()->first();
if ($latest) {
    echo "Latest Log Type: " . $latest->event_type . "\n";
} else {
    echo "No logs found in DB.\n";
}
