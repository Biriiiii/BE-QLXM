<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query()->latest();
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        $brands = $query->paginate(5);
        return BrandResource::collection($brands);
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }
        $brand = Brand::create($data);
        return new BrandResource($brand);
    }

    public function show($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Không tìm thấy hãng'], 404);
        }
        return new BrandResource($brand);
    }

    public function update(BrandRequest $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Không tìm thấy hãng'], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }
        $brand->update($data);
        return new BrandResource($brand);
    }

    public function destroy($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Không tìm thấy hãng'], 404);
        }
        $brand->delete();
        return response()->json(['message' => 'Xóa hãng thành công']);
    }
}
