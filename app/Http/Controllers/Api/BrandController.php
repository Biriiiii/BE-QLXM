<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
// Nếu bạn sử dụng Request Form Validation, hãy import BrandRequest:
use App\Http\Requests\BrandRequest;
// Nếu bạn sử dụng API Resources, hãy import BrandResource:
use App\Http\Resources\BrandResource;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class BrandController extends Controller
{
    /**
     * Lấy danh sách tất cả các Brands (GET /api/brands).
     */

    // ...

    public function index(Request $request)
    {
        // 1. Lấy giá trị per_page từ request, mặc định là 5
        $perPage = $request->get('per_page', 5);

        // 2. Sắp xếp và gọi paginate() TRỰC TIẾP
        //    Database sẽ xử lý việc phân trang
        $brands = Brand::orderBy('name')->paginate($perPage);

        // 3. Trả về BrandResource::collection
        //    Laravel sẽ tự động bọc Paginator (bao gồm data, links, meta)
        return BrandResource::collection($brands);
    }

    /**
     * Thêm một Brand mới (POST /api/brands).
     * Logic này được trích xuất từ xung đột HEAD.
     */
    // Sử dụng BrandRequest $request nếu đã định nghĩa Request Validation
    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 's3');
            if (!$path) {
                Log::error("S3 Upload Failed: Brand logo could not be stored.");
                return response()->json([
                    'success' => false,
                    'message' => 'LỖI S3: Không thể tải ảnh lên. Vui lòng kiểm tra lại cấu hình AWS và quyền truy cập bucket.',
                ], 500);
            }
            $data['logo'] = $path;
        }
        $brand = Brand::create($data);
        return new BrandResource($brand);
    }

    /**
     * Hiển thị chi tiết một Brand (GET /api/brands/{id}).
     */
    public function show($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }
        return new BrandResource($brand);
    }

    /**
     * Cập nhật thông tin Brand (PUT/PATCH /api/brands/{id}).
     * Logic này được trích xuất từ xung đột HEAD trong phương thức show.
     */
    // Sử dụng BrandRequest $request nếu đã định nghĩa Request Validation
    public function update(BrandRequest $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            // Bước 1: Xóa logo cũ
            if ($brand->logo) {
                Storage::disk('s3')->delete($brand->logo);
            }
            // Bước 2: Tải logo mới lên
            $path = $request->file('logo')->store('brands', 's3');
            if (!$path) {
                Log::error("S3 Upload Failed: Brand logo update could not be stored.");
                return response()->json([
                    'success' => false,
                    'message' => 'LỖI S3: Không thể tải ảnh mới lên. Vui lòng kiểm tra lại cấu hình AWS và quyền truy cập bucket.',
                ], 500);
            }
            $data['logo'] = $path;
        }

        // Bước 3: Cập nhật thông tin Brand trong DB
        $brand->update($data);
        return new BrandResource($brand);
    }

    /**
     * Xóa một Brand (DELETE /api/brands/{id}).
     * Thêm vào để hoàn thiện API Resource.
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        // Xóa logo khỏi S3 trước
        if ($brand->logo) {
            Storage::disk('s3')->delete($brand->logo);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully'
        ], 200);
    }
}
