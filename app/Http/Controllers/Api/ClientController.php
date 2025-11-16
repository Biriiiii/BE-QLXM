<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\OrderResource;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use \App\Http\Resources\CategoryResource;
use \App\Http\Resources\BrandResource;
use App\Models\Order;
use App\Models\Customer;
use App\Http\Requests\OrderStoreRequest;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function getCategories()
    {
        return CategoryResource::collection(Category::all());
    }

    public function getCategory($id)
    {
        return new CategoryResource(Category::findOrFail($id));
    }

    public function getBrands()
    {
        return BrandResource::collection(Brand::all());
    }

    public function getBrand($id)
    {
        return new BrandResource(Brand::findOrFail($id));
    }

    public function getProducts(Request $request)
    {
        $query = Product::with(['brand', 'category'])->latest();

        if ($request->filled('brand_id')) $query->where('brand_id', $request->brand_id);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('search')) $query->where('name', 'like', '%' . $request->search . '%');

        $perPage = $request->get('per_page', 10);
        return ProductResource::collection($query->paginate($perPage));
    }

    public function getProduct($id)
    {
        $product = Product::with(['brand', 'category'])->findOrFail($id);
        return new ProductResource($product);
    }

    public function getRelatedProducts(Request $request)
    {
        $query = Product::with(['brand', 'category'])
            ->where(function ($q) use ($request) {
                if ($request->filled('brand_id')) {
                    $q->where('brand_id', $request->brand_id);
                }
                if ($request->filled('category_id')) {
                    $q->orWhere('category_id', $request->category_id);
                }
            });
        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }
        $limit = $request->input('limit', 4);
        $products = $query->take($limit)->get();
        return ProductResource::collection($products);
    }
    public function createOrder(OrderStoreRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Tìm hoặc tạo customer
            $customer = Customer::updateOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['customer_address'],
                ]
            );

            // Tạo order
            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'order_date' => now(),
                'total_amount' => 0,
            ]);

            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Kiểm tra tồn kho
                if ($product->stock < $item['quantity']) {
                    throw new \Exception('Sản phẩm ' . $product->name . ' không đủ hàng (còn lại: ' . $product->stock . ')');
                }

                // Trừ tồn kho
                $product->decrement('stock', $item['quantity']);

                // Tạo order item
                $orderItem = $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $total += $product->price * $item['quantity'];
                $orderItems[] = $orderItem;
            }

            // Cập nhật tổng tiền
            $order->update(['total_amount' => $total]);

            // Load relationships cho email
            $order->load(['customer', 'items.product']);

            // Gửi email xác nhận (nếu có email)
            $emailSent = false;
            $emailError = null;

            if ($customer->email) {
                try {
                    Log::info("Attempting to send email to: " . $customer->email . " for order: " . $order->id);
                    Mail::to($customer->email)->send(new OrderConfirmation($order));
                    Log::info("Email confirmation sent successfully to: " . $customer->email . " for order: " . $order->id);
                    $emailSent = true;
                } catch (\Exception $mailException) {
                    $emailError = $mailException->getMessage();
                    Log::error("Failed to send email to: " . $customer->email . " - Error: " . $emailError);
                    Log::error("Mail Exception Stack Trace: " . $mailException->getTraceAsString());
                    // Không throw exception để không rollback order
                }
            } else {
                Log::info("No email provided for order: " . $order->id);
            }

            DB::commit();

            $message = 'Đặt hàng thành công!';
            if ($customer->email) {
                if ($emailSent) {
                    $message .= ' Email xác nhận đã được gửi.';
                } else {
                    $message .= ' Tuy nhiên không thể gửi email xác nhận.';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'email_sent' => $emailSent,
                'email_error' => $emailError,
                'data' => new OrderResource($order)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order creation failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Debug endpoint để test email
    public function testEmail(Request $request)
    {
        try {
            $email = $request->input('email', 'bangeabar@gmail.com');

            // Tìm order mới nhất để test
            $order = Order::with(['customer', 'items.product'])->latest()->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng nào để test'
                ], 404);
            }

            Log::info("Testing email send to: " . $email . " with order: " . $order->id);

            Mail::to($email)->send(new OrderConfirmation($order));

            Log::info("Test email sent successfully to: " . $email);

            return response()->json([
                'success' => true,
                'message' => 'Email test gửi thành công!',
                'email' => $email,
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            Log::error("Test email failed: " . $e->getMessage());
            Log::error("Test email stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Email test thất bại: ' . $e->getMessage(),
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
