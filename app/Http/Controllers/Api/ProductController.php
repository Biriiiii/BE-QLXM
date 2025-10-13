<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Cần thiết cho việc quản lý file trong update/store
// use App\Http\Requests\ProductRequest; // Import nếu cần validation
// use App\Http\Resources\ProductResource; // Import nếu sử dụng Resource

class ProductController extends Controller
{
    /**
     * Lấy danh sách sản phẩm với các bộ lọc, sắp xếp và phân trang (GET /api/products).
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Lọc theo từ khóa
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Lọc theo Category ID
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Lọc theo Brand ID
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->get('brand_id'));
        }

        // Lọc theo khoảng giá (min_price - giữ lại từ xung đột)
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }

        // Lọc theo khoảng giá (max_price - giữ lại từ xung đột)
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Phân trang và Eager Loading
        $perPage = $request->get('per_page', 12);
        $products = $query->with(['category', 'brand'])->paginate($perPage);

        // Trả về JSON Response (giữ lại từ nhánh hợp nhất)
        return response()->json([
            'success' => true,
            // 'data' => ProductResource::collection($products) // Dùng nếu sử dụng Resource
            'data' => $products
        ]);
    }

    /**
     * Thêm một Sản phẩm mới (POST /api/products).
     * Logic trích xuất từ xung đột HEAD.
     */
    public function store(Request $request)
    {
        // $data = $request->validated(); // Dùng nếu ProductRequest được sử dụng
        $data = $request->all();

        if ($request->hasFile('image')) {
            // Lưu ảnh vào S3
            $data['image'] = $request->file('image')->store('products', 's3');
        }

        $product = Product::create($data);

        // return new ProductResource($product); // Dùng nếu ProductResource được sử dụng
        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Hiển thị chi tiết một Sản phẩm (GET /api/products/{id}).
     */
    public function show($id)
    {
        $product = Product::with(['category', 'brand'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Cập nhật thông tin Sản phẩm (PUT/PATCH /api/products/{id}).
     * Logic trích xuất từ xung đột HEAD.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        // $data = $request->validated(); // Dùng nếu ProductRequest được sử dụng
        $data = $request->all();

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ (nếu có)
            if ($product->image) {
                Storage::disk('s3')->delete($product->image);
            }
            // Lưu ảnh mới
            $data['image'] = $request->file('image')->store('products', 's3');
        }

        $product->update($data);

        // return new ProductResource($product->load(['brand', 'category'])); // Dùng nếu Resource
        return response()->json([
            'success' => true,
            'data' => $product->load(['brand', 'category']),
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Xóa một Sản phẩm (DELETE /api/products/{id}).
     * Logic trích xuất từ xung đột HEAD.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        // Xóa ảnh khỏi S3 trước
        if ($product->image) {
            Storage::disk('s3')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Xóa sản phẩm thành công']);
    }
}
