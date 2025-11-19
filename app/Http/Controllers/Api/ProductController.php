<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Product;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Product management API endpoints"
 * )
 */
class ProductController extends Controller
{
    /**
     * Display a listing of products with pagination and sorting
     * 
     * @OA\Get(
     *      path="/api/products",
     *      operationId="getProductsList",
     *      tags={"Products"},
     *      summary="Get list of products",
     *      description="Returns list of products with pagination, sorting and filtering options",
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Number of products per page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              example=10
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              example=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="min_price",
     *          description="Minimum price filter",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *              format="float",
     *              example=10.50
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="max_price",
     *          description="Maximum price filter",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *              format="float",
     *              example=100.00
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="in_stock",
     *          description="Filter by stock availability (true/false)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean",
     *              example=true
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort_by",
     *          description="Sort by field (name, price, created_at)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="price"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort_order",
     *          description="Sort order (asc/desc)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="asc"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Products retrieved successfully"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="current_page",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="data",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(
     *                              property="id",
     *                              type="integer",
     *                              example=1
     *                          ),
     *                          @OA\Property(
     *                              property="name",
     *                              type="string",
     *                              example="Product 1"
     *                          ),
     *                          @OA\Property(
     *                              property="description",
     *                              type="string",
     *                              example="Product description"
     *                          ),
     *                          @OA\Property(
     *                              property="price",
     *                              type="number",
     *                              format="float",
     *                              example=29.99
     *                          ),
     *                          @OA\Property(
     *                              property="stock_quantity",
     *                              type="integer",
     *                              example=100
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('in_stock')) {
            if ($request->in_stock == 'true' || $request->in_stock == 1) {
                $query->where('stock_quantity', '>', 0);
            } else {
                $query->where('stock_quantity', 0);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort parameters
        $allowedSortColumns = ['name', 'price', 'created_at'];
        $allowedSortOrders = ['asc', 'desc'];
        
        if (in_array($sortBy, $allowedSortColumns) && in_array($sortOrder, $allowedSortOrders)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Apply pagination
        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        return $this->SuccessResponse($products, 'Products retrieved successfully');
    }

    /**
     * Store a newly created product
     * 
     * @OA\Post(
     *      path="/api/products",
     *      operationId="createProduct",
     *      tags={"Products"},
     *      summary="Create new product",
     *      description="Creates a new product",
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      example="Sample Product"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="This is a sample product"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example=29.99
     *                  ),
     *                  @OA\Property(
     *                      property="stock_quantity",
     *                      type="integer",
     *                      example=100
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *                  required={"name", "price", "stock_quantity"}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Product created successfully"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      example="Sample Product"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="This is a sample product"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example=29.99
     *                  ),
     *                  @OA\Property(
     *                      property="stock_quantity",
     *                      type="integer",
     *                      example=100
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      example="/storage/images/product_1234567890.jpg"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'product_' . time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('images', $filename, 'public');
            
            $imagePath = storage_path('app/public/' . $path);
            $resizedImage = \Intervention\Image\Laravel\Facades\Image::read($imagePath);
            $resizedImage->scale(width: 800);
            $resizedImage->save($imagePath);
            
            $validatedData['image'] = '/storage/' . $path;
        }

        $product = Product::create($validatedData);

        return $this->SuccessResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified product
     * 
     * @OA\Get(
     *      path="/api/products/{id}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Get product information",
     *      description="Returns product data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Product retrieved successfully"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      example="Sample Product"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="This is a sample product"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example=29.99
     *                  ),
     *                  @OA\Property(
     *                      property="stock_quantity",
     *                      type="integer",
     *                      example=100
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
     */
    public function show(Product $product)
    {
        return $this->SuccessResponse($product, 'Product retrieved successfully');
    }

    /**
     * Update the specified product
     * 
     * @OA\Put(
     *      path="/api/products/{id}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Update existing product",
     *      description="Update product data",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      example="Updated Product"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="This is an updated product"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example=39.99
     *                  ),
     *                  @OA\Property(
     *                      property="stock_quantity",
     *                      type="integer",
     *                      example=50
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Product updated successfully"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      example="Updated Product"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      example="This is an updated product"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number",
     *                      format="float",
     *                      example=39.99
     *                  ),
     *                  @OA\Property(
     *                      property="stock_quantity",
     *                      type="integer",
     *                      example=50
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      example="/storage/images/product_1234567890.jpg"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error"
     *      )
     * )
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'product_' . time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('images', $filename, 'public');
            
            $imagePath = storage_path('app/public/' . $path);
            $resizedImage = \Intervention\Image\Laravel\Facades\Image::read($imagePath);
            $resizedImage->scale(width: 800);
            $resizedImage->save($imagePath);
            
            $validatedData['image'] = '/storage/' . $path;
        }

        $product->update($validatedData);

        return $this->SuccessResponse($product, 'Product updated successfully');
    }

    /**
     * Remove the specified product
     * 
     * @OA\Delete(
     *      path="/api/products/{id}",
     *      operationId="deleteProduct",
     *      tags={"Products"},
     *      summary="Delete product",
     *      description="Remove product from database",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Product deleted successfully"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->SuccessResponse(null, 'Product deleted successfully');
    }
}