<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Lấy danh sách category
    public function index()
    {
        return response()->json(Category::all());
    }

    // Xem chi tiết category
    public function show($id)
    {
        $category = Category::withCount('products')->find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        return new CategoryResource($category);
    }

    // Tạo mới category
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $category = Category::create($data);
        return new CategoryResource($category);
    }

    // Sửa category
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        $data = $request->validated();
        $category->update($data);
        return new CategoryResource($category);
    }

    // Xóa category
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
    }
}
