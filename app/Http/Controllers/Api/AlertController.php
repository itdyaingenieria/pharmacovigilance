<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlertSendRequest;
use App\Http\Requests\BulkAlertSendRequest;
use App\Http\Resources\AlertResource;
use App\Models\Order;
use App\Services\AlertService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AlertController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly AlertService $alertService) {}

    public function send(AlertSendRequest $request)
    {
        $validated = $request->validated();
        $order = Order::query()->with(['customer', 'items.medication'])->findOrFail($validated['order_id']);

        Log::channel('stderr')->info('Alert send request received.', [
            'order_id' => $order->id,
            'lot_number' => $validated['lot_number'],
            'triggered_by_user_id' => $request->user()?->id,
        ]);

        $alert = $this->alertService->sendOrderAlert(
            $request->user(),
            $order,
            $validated['lot_number'],
            (bool) ($validated['force'] ?? false)
        );

        if ($alert->status === 'skipped_duplicate') {
            Log::channel('stderr')->warning('Alert send skipped (duplicate).', [
                'alert_id' => $alert->id,
                'order_id' => $order->id,
                'lot_number' => $validated['lot_number'],
            ]);

            return $this->responseError(
                ['alert' => new AlertResource($alert)],
                'Alert skipped: an alert was already sent for this order and lot.',
                Response::HTTP_CONFLICT
            );
        }

        if ($alert->status === 'failed') {
            Log::channel('stderr')->error('Alert send failed.', [
                'alert_id' => $alert->id,
                'order_id' => $order->id,
                'lot_number' => $validated['lot_number'],
                'error' => $alert->error_message,
            ]);

            return $this->responseError(
                ['alert' => new AlertResource($alert)],
                'Unable to send alert email.',
                Response::HTTP_BAD_REQUEST
            );
        }

        Log::channel('stderr')->info('Alert send completed successfully.', [
            'alert_id' => $alert->id,
            'order_id' => $order->id,
            'lot_number' => $validated['lot_number'],
        ]);

        return $this->responseSuccess(new AlertResource($alert), 'Alert sent successfully.');
    }

    public function sendBulk(BulkAlertSendRequest $request)
    {
        $validated = $request->validated();

        $orders = Order::query()
            ->with(['customer', 'items.medication'])
            ->whereIn('id', $validated['order_ids'])
            ->get();

        $result = $this->alertService->sendBulkAlerts(
            $request->user(),
            $orders,
            $validated['lot_number'],
            (bool) ($validated['force'] ?? false)
        );

        return $this->responseSuccess([
            'summary' => $result['summary'],
            'total' => $result['total'],
            'alerts' => AlertResource::collection(collect($result['alerts'])),
        ], 'Bulk alert process completed.');
    }
}
