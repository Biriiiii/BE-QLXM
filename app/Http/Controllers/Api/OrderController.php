<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer; // Imported Customer model for cleaner use
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng với các bộ lọc tùy chọn.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
     * Tạo mới đơn hàng và trừ tồn kho sản phẩm.
     * Sử dụng DB::transaction để đảm bảo tính toàn vẹn dữ liệu.
     *
     * @param OrderStoreRequest $request
     * @return OrderResource|\Illuminate\Http\JsonResponse
     */
    public function store(OrderStoreRequest $request)
    {
        // Bắt đầu transaction để đảm bảo tất cả các thao tác DB được thực hiện hoặc không có thao tác nào
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $customerId = null;

            // Xử lý tạo mới customer nếu customer_id không được cung cấp
            if (empty($data['customer_id'])) {
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'],
                    'email' => $data['customer_email'] ?? null,
                ]);
                $customerId = $customer->id;
            } else {
                $customerId = $data['customer_id'];
            }

            // Tạo đơn hàng chính
            $order = Order::create([
                'customer_id' => $customerId,
                'status' => 'pending_deposit',
                'total_amount' => 0,
                'deposit_amount' => 0,
                'installment_term' => $data['installment_term'] ?? null,
                'installment_amount' => $data['installment_amount'] ?? null,
                'order_date' => Carbon::now(), // Thêm ngày đặt hàng
            ]);

            $total = 0;
            // Xử lý các mặt hàng, trừ tồn kho và tính tổng
            foreach ($data['items'] as $item) {
                // Sử dụng findOrFail để báo lỗi 404 nếu sản phẩm không tồn tại
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    DB::rollBack(); // Hoàn tác nếu không đủ tồn kho
                    return response()->json([
                        'message' => 'Sản phẩm ' . $product->name . ' (ID: ' . $product->id . ') không đủ tồn kho. Tồn kho hiện tại: ' . $product->stock
                    ], 422);
                }

                // Giảm tồn kho và tính tổng
                $product->decrement('stock', $item['quantity']);
                $lineTotal = $product->price * $item['quantity'];
                $total += $lineTotal;

                // Tạo chi tiết đơn hàng (Order Item)
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            // Cập nhật tổng số tiền và tiền đặt cọc (30% tổng giá trị)
            $deposit = round($total * 0.3, 2);
            $order->update([
                'total_amount' => $total,
                'deposit_amount' => $deposit,
            ]);

            // Commit transaction nếu mọi thứ thành công
            DB::commit();

            $order->load(['items.product', 'customer']);
            return new OrderResource($order);
        } catch (\Throwable $e) {
            DB::rollBack(); // Hoàn tác nếu có bất kỳ lỗi nào xảy ra

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
     *
     * @param int $id
     * @return OrderResource
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'customer'])->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     *
     * @param OrderStatusRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(OrderStatusRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->validated()['status']]);
        return response()->json(['message' => 'Cập nhật trạng thái thành công']);
    }

    /**
     * Xóa đơn hàng và phục hồi tồn kho (nếu đơn hàng chưa bị hủy).
     * Sử dụng DB::transaction để đảm bảo tính toàn vẹn.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $order = Order::with('items.product')->findOrFail($id);

            // Chỉ phục hồi tồn kho nếu trạng thái đơn hàng KHÔNG PHẢI là 'cancelled'
            // (giả định rằng stock đã được phục hồi khi chuyển sang 'cancelled' hoặc stock vẫn đang bị trừ)
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
