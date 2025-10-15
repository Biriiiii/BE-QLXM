<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::with('customer');

        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('customer_id')) $q->where('customer_id', $request->customer_id);
        if ($request->filled('from')) $q->whereDate('order_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('order_date', '<=', $request->to);

        return OrderResource::collection($q->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:100',
            'customer_phone' => 'required_without:customer_id|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'installment_term' => 'nullable|string',
            'installment_amount' => 'nullable|numeric|min:0',
        ]);

        // Nếu không có customer_id, tạo mới customer
        if (empty($data['customer_id'])) {
            $customer = \App\Models\Customer::create([
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'],
                'email' => $data['customer_email'] ?? null,
                'address' => $data['customer_address'] ?? null,
            ]);
            $customerId = $customer->id;
        } else {
            $customerId = $data['customer_id'];
        }

        $order = Order::create([
            'customer_id' => $customerId,
            'order_date' => now(),
            'status' => 'pending_deposit',
            'total_amount' => 0,
            'deposit_amount' => 0,
            'installment_term' => $data['installment_term'] ?? null,
            'installment_amount' => $data['installment_amount'] ?? null,
        ]);

        $total = 0;
        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) throw new \Exception('Product not found');
            if ($product->stock < $item['quantity']) {
                return response()->json(['message' => 'Sản phẩm không đủ tồn kho'], 422);
            }

            $product->decrement('stock', $item['quantity']);
            $lineTotal = $product->price * $item['quantity'];
            $total += $lineTotal;

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }
        $deposit = round($total * 0.3, 2);
        $order->update([
            'total_amount' => $total,
            'deposit_amount' => $deposit,
        ]);

        $order->load(['items.product', 'customer']);
        return new OrderResource($order);
    }

    public function show($id)
    {
        $order = Order::with(['items.product', 'customer'])->findOrFail($id);
        return new OrderResource($order);
    }

    public function updateStatus(OrderStatusRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->validated()['status']]);
        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    public function destroy($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        if ($order->status !== 'cancelled') {
            foreach ($order->items as $it) {
                $it->product->increment('stock', $it->quantity);
            }
        }

        $order->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
