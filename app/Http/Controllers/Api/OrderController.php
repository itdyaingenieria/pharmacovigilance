<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationSearchRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderSearchService;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly OrderSearchService $searchService) {}

    public function index(MedicationSearchRequest $request)
    {
        $validated = $request->validated();

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : now()->subDays(30);

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])
            : now();

        $perPage = (int) ($validated['per_page'] ?? 15);
        $orders = $this->searchService->search($validated['lot'], $startDate, $endDate, $perPage);

        return $this->responseSuccess([
            'filters' => [
                'lot' => $validated['lot'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'items' => OrderResource::collection($orders->getCollection()),
        ], 'Orders retrieved successfully.');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.medication']);

        return $this->responseSuccess(new OrderResource($order), 'Order details retrieved.');
    }

    public function exportCsv(MedicationSearchRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : now()->subDays(30);

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])
            : now();

        $lot = $validated['lot'];
        $orders = $this->searchService->forExport($lot, $startDate, $endDate);
        $filename = sprintf('pharmacovigilance-orders-%s.csv', now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($orders, $lot) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'order_id',
                'purchase_date',
                'customer_name',
                'customer_email',
                'customer_phone',
                'medication_name',
                'lot_number',
                'quantity',
            ]);

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if ($item->medication?->lot_number !== $lot) {
                        continue;
                    }

                    fputcsv($output, [
                        $order->id,
                        optional($order->purchase_date)->toDateTimeString(),
                        $order->customer?->name,
                        $order->customer?->email,
                        $order->customer?->phone,
                        $item->medication?->name,
                        $item->medication?->lot_number,
                        $item->quantity,
                    ]);
                }
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
