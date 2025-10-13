<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
// Nếu bạn sử dụng Request Form Validation, hãy import BrandRequest:
// use App\Http\Requests\BrandRequest; 
// Nếu bạn sử dụng API Resources, hãy import BrandResource:
// use App\Http\Resources\BrandResource; 
use Illuminate\Support\Facades\Storage;


class BrandController extends Controller
{
    /**
     * Lấy danh sách tất cả các Brands (GET /api/brands).
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        // Thực hiện truy vấn và sắp xếp (giữ lại từ nhánh hợp nhất)
        $brands = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * Thêm một Brand mới (POST /api/brands).
     * Logic này được trích xuất từ xung đột HEAD.
     */
    // Sử dụng BrandRequest $request nếu đã định nghĩa Request Validation
    public function store(Request $request)
    {
        // $data = $request->validated(); // Dùng nếu BrandRequest được sử dụng
        $data = $request->all(); // Dùng tạm nếu chỉ sử dụng Request

        if ($request->hasFile('logo')) {
            // Lưu logo vào S3 (dựa trên logic trong xung đột)
            $data['logo'] = $request->file('logo')->store('brands', 's3');
        }

        $brand = Brand::create($data);

        // return new BrandResource($brand); // Dùng nếu BrandResource được sử dụng
        return response()->json([
            'success' => true,
            'data' => $brand,
            'message' => 'Brand created successfully'
        ], 201);
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

        return response()->json([
            'success' => true,
            'data' => $brand
        ]);
    }

    /**
     * Cập nhật thông tin Brand (PUT/PATCH /api/brands/{id}).
     * Logic này được trích xuất từ xung đột HEAD trong phương thức show.
     */
    // Sử dụng BrandRequest $request nếu đã định nghĩa Request Validation
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        // $data = $request->validated(); // Dùng nếu BrandRequest được sử dụng
        $data = $request->all(); // Dùng tạm nếu chỉ sử dụng Request

        if ($request->hasFile('logo')) {
            // Xóa logo cũ (nếu có)
            if ($brand->logo) {
                Storage::disk('s3')->delete($brand->logo);
            }
            // Lưu logo mới
            $data['logo'] = $request->file('logo')->store('brands', 's3');
        }

        $brand->update($data);

        // return new BrandResource($brand); // Dùng nếu BrandResource được sử dụng
        return response()->json([
            'success' => true,
            'data' => $brand,
            'message' => 'Brand updated successfully'
        ]);
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
