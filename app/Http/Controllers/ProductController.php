<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LDAP\Result;
use Mockery\Expectation;
/**
 * @OA\Info(
 *     title="Product API",
 *     version="1.0",
 *     description="API documentation for Product management"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product APIs"
 * )
 */
class ProductController extends Controller
{



    /**
     * @OA\Get(
     *     path="/api/all-products",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     @OA\Response(response=200, description="Successful operation")
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
     * @OA\Post(
     *   path="/api/store",
     *   tags={"Products"},
     *   summary="Store the product details using API",
     *   description="This endpoint allows storing product details via the API.",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               required={"name", "price", "stock", "description", "images"},
     *               @OA\Property(property="name", type="string", example="iPhone 15"),
     *               @OA\Property(property="images", type="string", format="binary"),
     *               @OA\Property(property="price", type="number", format="float", example="999.99"),
     *               @OA\Property(property="stock", type="integer", example=10),
     *               @OA\Property(property="description", type="string", example="Latest model with A17 chip.")
     *           )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Product added successfully",
     *       @OA\JsonContent(
     *           @OA\Property(property="status", type="string", example="success"),
     *           @OA\Property(property="message", type="string", example="Product added Successfully!")
     *       )
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="Bad request (missing image or validation failed)",
     *       @OA\JsonContent(
     *           @OA\Property(property="status", type="string", example="error"),
     *           @OA\Property(property="message", type="string", example="Product Image is required!")
     *       )
     *   ),
     *   @OA\Response(
     *       response=500,
     *       description="Internal server error"
     *   )
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
     *     path="/api/show/{id}",
     *     tags={"Products"},
     *     summary="Display the single product details using API",
     *     description="This endpoint retrieves a single product's details by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="No Product Found!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
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
     *   path="/api/update",
     *   tags={"Products"},
     *   summary="Edit the product details using API",
     *   description="This endpoint allows editing the details of an existing product by its ID.",
     *   @OA\Parameter(
     *       name="id",
     *       in="query",
     *       required=true,
     *       description="ID of the product to update",
     *       @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               required={"name", "price", "stock", "description"},
     *               @OA\Property(property="name", type="string", example="iPhone 15"),
     *               @OA\Property(property="images", type="string", format="binary"),
     *               @OA\Property(property="price", type="number", format="float", example="999.99"),
     *               @OA\Property(property="stock", type="integer", example=10),
     *               @OA\Property(property="description", type="string", example="Latest model with A17 chip.")
     *           )
     *       )
     *   ),
     *   @OA\Response(
     *       response=200,
     *       description="Product updated successfully",
     *       @OA\JsonContent(
     *           @OA\Property(property="status", type="string", example="success"),
     *           @OA\Property(property="message", type="string", example="Product updated successfully.")
     *       )
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="Bad request (invalid data or validation failed)",
     *       @OA\JsonContent(
     *           @OA\Property(property="status", type="string", example="error"),
     *           @OA\Property(property="message", type="string", example="Validation failed")
     *       )
     *   ),
     *   @OA\Response(
     *       response=404,
     *       description="Product not found",
     *       @OA\JsonContent(
     *           @OA\Property(property="status", type="string", example="error"),
     *           @OA\Property(property="message", type="string", example="Product not found")
     *       )
     *   ),
     *   @OA\Response(
     *       response=500,
     *       description="Internal server error"
     *   )
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
     *    tags={"Products"},
     *    summary="Delete the product by ID using API",
     *    description="This endpoint deletes a product by its ID.",
     *    @OA\Parameter(
     *        name="id",
     *        in="path",
     *        required=true,
     *        description="ID of the product to delete",
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Response(
     *        response=200,
     *        description="Product deleted successfully",
     *        @OA\JsonContent(
     *            @OA\Property(property="status", type="string", example="success"),
     *            @OA\Property(property="message", type="string", example="Product deleted successfully")
     *        )
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="Product not found",
     *        @OA\JsonContent(
     *            @OA\Property(property="status", type="string", example="error"),
     *            @OA\Property(property="message", type="string", example="Product not found")
     *        )
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal server error"
     *    )
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