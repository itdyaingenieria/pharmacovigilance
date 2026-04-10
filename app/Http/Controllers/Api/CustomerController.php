<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Traits\ResponseTrait;

class CustomerController extends Controller
{
    use ResponseTrait;

    public function show(Customer $customer)
    {
        return $this->responseSuccess(new CustomerResource($customer), 'Customer details retrieved.');
    }
}
