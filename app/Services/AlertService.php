<?php

namespace App\Services;

use App\Mail\PharmacovigilanceAlertMail;
use App\Models\Alert;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertService
{
    public function sendOrderAlert(User $triggeredBy, Order $order, string $lot, bool $force = false): Alert
    {
        $order->loadMissing(['customer', 'items.medication']);

        $lastSentAlert = Alert::query()
            ->where('order_id', $order->id)
            ->where('customer_id', $order->customer_id)
            ->where('lot_number', $lot)
            ->where('status', 'sent')
            ->latest('id')
            ->first();

        if ($lastSentAlert && !$force) {
            return Alert::create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'user_id' => $triggeredBy->id,
                'medication_id' => $lastSentAlert->medication_id,
                'lot_number' => $lot,
                'channel' => 'email',
                'status' => 'skipped_duplicate',
                'metadata' => [
                    'previous_alert_id' => $lastSentAlert->id,
                    'customer_email' => $order->customer?->email,
                ],
            ]);
        }

        $affectedMedication = $order->items
            ->first(fn($item) => $item->medication?->lot_number === $lot)?->medication;

        if (!$affectedMedication) {
            return Alert::create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'user_id' => $triggeredBy->id,
                'lot_number' => $lot,
                'channel' => 'email',
                'status' => 'failed',
                'error_message' => 'The order does not include medications from the provided lot.',
                'metadata' => [
                    'customer_email' => $order->customer?->email,
                ],
            ]);
        }

        $payload = [
            'order' => $order,
            'customer' => $order->customer,
            'medication' => $affectedMedication,
            'lot_number' => $lot,
            'recommended_action' => 'Stop using the medication and contact the pharmacy immediately for clinical guidance.',
        ];

        try {
            Mail::to($order->customer->email)->send(new PharmacovigilanceAlertMail($payload));

            Log::channel('stderr')->info('Alert email sent successfully.', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'customer_email' => $order->customer->email,
                'lot_number' => $lot,
                'triggered_by_user_id' => $triggeredBy->id,
            ]);

            Log::info('Alert email sent successfully.', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'customer_email' => $order->customer->email,
                'lot_number' => $lot,
                'triggered_by_user_id' => $triggeredBy->id,
            ]);

            return Alert::create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'user_id' => $triggeredBy->id,
                'medication_id' => $affectedMedication?->id,
                'lot_number' => $lot,
                'channel' => 'email',
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'customer_email' => $order->customer->email,
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::channel('stderr')->error('Failed to send alert email.', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'customer_email' => $order->customer?->email,
                'lot_number' => $lot,
                'triggered_by_user_id' => $triggeredBy->id,
                'error' => $exception->getMessage(),
            ]);

            Log::error('Failed to send alert email.', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'customer_email' => $order->customer?->email,
                'lot_number' => $lot,
                'triggered_by_user_id' => $triggeredBy->id,
                'error' => $exception->getMessage(),
            ]);

            return Alert::create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'user_id' => $triggeredBy->id,
                'medication_id' => $affectedMedication?->id,
                'lot_number' => $lot,
                'channel' => 'email',
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'metadata' => [
                    'customer_email' => $order->customer->email,
                ],
            ]);
        }
    }

    public function sendBulkAlerts(User $triggeredBy, iterable $orders, string $lot, bool $force = false): array
    {
        $alerts = [];
        $summary = [
            'sent' => 0,
            'skipped_duplicate' => 0,
            'failed' => 0,
        ];

        foreach ($orders as $order) {
            $alert = $this->sendOrderAlert($triggeredBy, $order, $lot, $force);
            $alerts[] = $alert;

            if (array_key_exists($alert->status, $summary)) {
                $summary[$alert->status]++;
            }
        }

        return [
            'alerts' => $alerts,
            'summary' => $summary,
            'total' => count($alerts),
        ];
    }
}
