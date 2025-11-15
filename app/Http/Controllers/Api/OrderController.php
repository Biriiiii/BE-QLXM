<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

// --- BẮT ĐẦU THÊM VÀO ---
use Illuminate\Support\Facades\Mail; // <-- Thêm Mail Facade
use App\Mail\OrderPlaced; // <-- Thêm Mailable (sẽ tạo ở file 2)
// --- KẾT THÚC THÊM VÀO ---


class OrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng...
     */
    public function index(Request $request)
    {
        $q = Order::with('customer');

        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('customer_id')) $q->where('customer_id', $request->customer_id);
        if ($request->filled('from')) $q->whereDate('order_date', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('order_date', '<=', $request->to);

        return OrderResource::collection($q->latest()->paginate(15));
    }

    /**
     * Tạo mới đơn hàng...
     */
    public function store(OrderStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $customerId = null;
            $customerEmail = null; // <-- Biến tạm để giữ email

            if (!empty($data['customer_phone'])) {
                $customer = Customer::updateOrCreate(
                    ['phone' => $data['customer_phone']],
                    [
                        'name' => $data['customer_name'],
                        'email' => $data['customer_email'] ?? null, // <-- Lấy email
                        'address' => $data['customer_address'] ?? null,
                    ]
                );
                $customerId = $customer->id;
                $customerEmail = $customer->email; // <-- Lưu email lại
            } else if (!empty($data['customer_id'])) {
                $customerId = $data['customer_id'];
                $customer = Customer::find($customerId);
                if ($customer) $customerEmail = $customer->email; // <-- Lấy email từ customer cũ
            } else {
                return response()->json([
                    'message' => 'Vui lòng nhập số điện thoại khách hàng.'
                ], 422);
            }

            $order = Order::create([
                'customer_id' => $customerId,
                'status' => 'pending_deposit',
                'total_amount' => 0,
                'deposit_amount' => 0,
                'installment_term' => $data['installment_term'] ?? null,
                'installment_amount' => $data['installment_amount'] ?? null,
                'order_date' => Carbon::now(),
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Sản phẩm ' . $product->name . ' (ID: ' . $product->id . ') không đủ tồn kho. Tồn kho hiện tại: ' . $product->stock
                    ], 422);
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

            // Commit transaction nếu mọi thứ thành công
            DB::commit();

            // Tải lại thông tin để gửi mail và trả về response
            $order->load(['items.product', 'customer']);


            // --- BẮT ĐẦU THÊM CODE GỬI MAIL ---
            try {
                // $customerEmail đã được lấy ở trên
                if (!empty($customerEmail)) {
                    Mail::to($customerEmail)
                        ->send(new OrderPlaced($order)); // Dùng Mailable
                } else {
                    Log::info('Order ' . $order->id . ' created but no customer email to send to.');
                }
            } catch (\Exception $e) {
                // Quan trọng: Ghi log lỗi mail, nhưng KHÔNG rollBack
                // vì đơn hàng đã thành công.
                Log::error('Gửi email đơn hàng ' . $order->id . ' thất bại: ' . $e->getMessage());
            }
            // --- KẾT THÚC CODE GỬI MAIL ---


            return new OrderResource($order);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OrderController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            $statusCode = 500;
            $message = 'Đã xảy ra lỗi khi tạo đơn hàng.';

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
                $message = 'Không tìm thấy sản phẩm hoặc tài nguyên liên quan.';
            }

            return response()->json([
                'message' => $message,
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Hiển thị chi tiết đơn hàng.
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'customer'])->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(OrderStatusRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->validated()['status']]);
        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    /**
     * Xóa đơn hàng...
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $order = Order::with('items.product')->findOrFail($id);
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }
            $order->delete();
            DB::commit();
            return response()->json(['message' => 'Đã xóa đơn hàng thành công và phục hồi tồn kho.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('OrderController@destroy error: ' . $e->getMessage(), ['id' => $id]);
            $statusCode = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500;
            $message = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 'Không tìm thấy đơn hàng cần xóa.' : 'Đã xảy ra lỗi khi xóa đơn hàng.';
            return response()->json([
                'message' => $message,
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
