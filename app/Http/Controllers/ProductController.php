<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LDAP\Result;
use Mockery\Expectation;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/all-products",
     *      summary="Return all the products list using API",
     *      description="This endpoint retrieves a list of all products.",
     * )
     */
    public function index()
    {
        try {
            $data = Cache::remember('products', 60, fn() => Product::all());
            if ($data->count() == 0) {
                return response()->json(['status' => 'error', 'message' => 'No Product Found!']);
            }
            return response()->json(['data' => $data, 'status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/store",
     *   summary="Store the product details using API",
     *   description="This endpoint allows storing product details via the API.",
     * )
     */ 
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'images' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'description' => 'required|string'
            ]);
            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $file_name = time() . '_' . $image->getClientOriginalName();
                $file_path = $image->storeAs('products', $file_name, 'public');
                $validatedData['images'] = $file_path;
            } else {
                return response()->json(['status' => 'error', 'message' => 'Product Image is required!'], 400);
            }
            $result = Product::create($validatedData);
            return response()->json(['status' => 'success', 'message' => "Product added Successfully!"], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *   path="api/show/{id}",
     *   summary="Display the single product details using API",
     *   description="This endpoint retrieves a single product's details by its ID.",
     * )
     */ 
    public function show($id)
    {
        try {
            $data = Product::findOrFail($id);
            if ($data->count() == 0) {
                return response()->json(['status' => 'error', 'message' => 'No Product Found!']);
            }
            return response()->json(['data' => $data, 'status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *   path="api/update",
     *   summary="Edit the product details using API",
     *   description="This endpoint allows editing the details of an existing product by its ID.",
     * )
     */
    public function edit(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'images' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'description' => 'required|string'
            ]);
            $product = Product::findOrFail($request->id);
            if ($request->hasFile('images')) {
                $image = $request->file('images');
                $file_name = time() . '_' . $image->getClientOriginalName();
                $file_path = $image->storeAs('products', $file_name, 'public');
                $validatedData['images'] = $file_path;
            }
            $product->update($validatedData);
            return response()->json(['status' => 'success', 'message' => 'Product updated successfully.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    
    /**
     * @OA\Delete(
     *    path="/api/destroy/{id}",
     *    summary="Delete the product by ID using API",
     *    description="This endpoint deletes a product by its ID.",
     * )
     */
    public function destroy($id)
    {
        try {
            Product::destroy($id);
            return response()->json(['status' => 'success', 'message' => 'Product deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}