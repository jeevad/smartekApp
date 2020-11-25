<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'sku' => 'required|max:255',
            'description' => 'required',
            'product_img' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 422);
        }
        // dd($data);
        try {
            $filePath = '';
            if ($request->has('product_img')) {
                $image = $request->file('product_img');
                $name = Str::slug($request->input('name')) . '_' . time();
                $folder = '/uploads/images/';
                $filePath = $folder . $name . '.' . $image->getClientOriginalExtension();
                $this->uploadImg($image, $folder, 'public', $name);
            }
            $data['image'] = $filePath;
            $product = Product::create($data);
            Log::info('Product created');
            return response(['product' => new ProductResource($product), 'message' => 'Product created successfully'], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response(['error' => $e->getMessage(), 'Query Exception'], 500);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response(['error' => $e->getMessage(), 'Something went wrong'], 500);
        }
    }

    public function uploadImg(UploadedFile $uploadedFile, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);

        $file = $uploadedFile->storeAs($folder, $name . '.' . $uploadedFile->getClientOriginalExtension(), $disk);

        return $file;
    }
}
