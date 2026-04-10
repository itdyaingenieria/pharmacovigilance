<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class OrderSearchService
{
    public function search(string $lot, Carbon $startDate, Carbon $endDate, int $perPage = 15)
    {
        return $this->baseQuery($lot, $startDate, $endDate)
            ->with(['customer', 'items.medication'])
            ->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderByDesc('purchase_date')
            ->paginate($perPage);
    }

    public function forExport(string $lot, Carbon $startDate, Carbon $endDate)
    {
        return $this->baseQuery($lot, $startDate, $endDate)
            ->with(['customer', 'items.medication'])
            ->orderByDesc('purchase_date')
            ->get();
    }

    private function baseQuery(string $lot, Carbon $startDate, Carbon $endDate)
    {
        return Order::query()
            ->whereBetween('purchase_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereHas('items.medication', function ($query) use ($lot) {
                $query->where('lot_number', $lot);
            });
    }
}
