<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

<<<<<<< HEAD
    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 's3');
        }
        $brand = Brand::create($data);
        return new BrandResource($brand);
=======
        $brands = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
>>>>>>> 40c6a89abd3db13e6e79c6be8c85828395c54c8c
    }

    public function show($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }
<<<<<<< HEAD
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 's3');
        }
        $brand->update($data);
        return new BrandResource($brand);
    }
=======
>>>>>>> 40c6a89abd3db13e6e79c6be8c85828395c54c8c

        return response()->json([
            'success' => true,
            'data' => $brand
        ]);
    }
}