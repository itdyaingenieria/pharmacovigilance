<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlertSendRequest;
use App\Http\Requests\BulkAlertSendRequest;
use App\Http\Resources\AlertResource;
use App\Models\Order;
use App\Services\AlertService;
use App\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\Response;

class AlertController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly AlertService $alertService) {}

    public function send(AlertSendRequest $request)
    {
        $validated = $request->validated();
        $order = Order::query()->with(['customer', 'items.medication'])->findOrFail($validated['order_id']);

        $alert = $this->alertService->sendOrderAlert(
            $request->user(),
            $order,
            $validated['lot_number'],
            (bool) ($validated['force'] ?? false)
        );

        if ($alert->status === 'skipped_duplicate') {
            return $this->responseError(
                ['alert' => new AlertResource($alert)],
                'Alert skipped: an alert was already sent for this order and lot.',
                Response::HTTP_CONFLICT
            );
        }

        if ($alert->status === 'failed') {
            return $this->responseError(
                ['alert' => new AlertResource($alert)],
                'Unable to send alert email.',
                Response::HTTP_BAD_REQUEST
            );
        }

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
