<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Lấy danh sách sản phẩm với các bộ lọc, sắp xếp và phân trang (GET /api/products).
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->get('brand_id'));
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Cần đảm bảo cột $sortBy tồn tại, nếu không Laravel sẽ báo lỗi.
        // Tuy nhiên, để tránh phức tạp, chúng ta chấp nhận rủi ro và dựa vào convention.
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 12);
        // Eager load các mối quan hệ để tránh N+1 query
        $products = $query->with(['category', 'brand'])->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Thêm một Sản phẩm mới (POST /api/products).
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 's3');
            if (!$path) {
                Log::error("S3 Upload Failed: Product image could not be stored.");
                return response()->json([
                    'success' => false,
                    'message' => 'LỖI S3: Không thể tải ảnh sản phẩm lên. Vui lòng kiểm tra lại cấu hình AWS và quyền truy cập bucket.',
                ], 500);
            }
            $data['image'] = $path;
        }

        $product = Product::create($data);
        // Load lại mối quan hệ sau khi tạo
        return new ProductResource($product->load(['brand', 'category']));
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
        return new ProductResource($product);
    }

    /**
     * Cập nhật thông tin Sản phẩm (PUT/PATCH /api/products/{id}).
     */
    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        $data = $request->validated();

        // Logic xử lý file ảnh
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ khỏi S3
            if ($product->image) {
                Storage::disk('s3')->delete($product->image);
            }
            // Tải ảnh mới lên S3
            $path = $request->file('image')->store('products', 's3');
            if (!$path) {
                Log::error("S3 Upload Failed: Product image update could not be stored.");
                return response()->json([
                    'success' => false,
                    'message' => 'LỖI S3: Không thể tải ảnh mới lên. Vui lòng kiểm tra lại cấu hình AWS và quyền truy cập bucket.',
                ], 500);
            }
            $data['image'] = $path;
        }

        $product->update($data);

        // Load lại mối quan hệ sau khi cập nhật
        return new ProductResource($product->load(['brand', 'category']));
    }

    /**
     * Xóa một Sản phẩm (DELETE /api/products/{id}).
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        // Xóa ảnh khỏi S3 trước khi xóa record
        if ($product->image) {
            Storage::disk('s3')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Xóa sản phẩm thành công'], 200);
    }
}
