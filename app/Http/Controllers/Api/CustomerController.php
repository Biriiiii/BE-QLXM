<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\OrderResource;
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

    /**
     * Lấy tất cả đơn hàng của một khách hàng.
     *
     * @param int $id
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function orders($id)
    {
        $customer = Customer::findOrFail($id);

        $orders = $customer->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }
}
