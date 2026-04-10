<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicationSearchRequest;
use App\Http\Resources\MedicationResource;
use App\Models\Medication;
use App\Traits\ResponseTrait;

class MedicationController extends Controller
{
    use ResponseTrait;

    public function search(MedicationSearchRequest $request)
    {
        $lot = $request->validated()['lot'];

        $medications = Medication::query()
            ->where('lot_number', $lot)
            ->orderBy('name')
            ->get();

        return $this->responseSuccess([
            'lot' => $lot,
            'count' => $medications->count(),
            'items' => MedicationResource::collection($medications),
        ], 'Medication search completed.');
    }
}
