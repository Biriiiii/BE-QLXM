<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::latest()->paginate(15);
    }

    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return response()->json($customer, 201);
    }

    public function show($id)
    {
        $customer = Customer::withCount('orders')->findOrFail($id);
        return $customer;
    }

    public function update(CustomerRequest $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->validated());
        return $customer;
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
