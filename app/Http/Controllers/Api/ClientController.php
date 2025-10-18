<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use \App\Http\Resources\CategoryResource;
use \App\Http\Resources\BrandResource;
use App\Models\Order;
use App\Models\Customer;
use App\Http\Requests\OrderStoreRequest;
use Illuminate\Support\Facades\DB;
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

        return ProductResource::collection($query->paginate(10));
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
                'total' => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception('Sản phẩm ' . $product->name . ' không đủ hàng');
                }
                $product->stock -= $item['quantity'];
                $product->save();

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                $total += $product->price * $item['quantity'];
            }
            $order->total = $total;
            $order->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Đặt hàng thành công!'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
