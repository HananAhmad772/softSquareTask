<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;

class ImageController extends Controller
{

    /**
     * Upload and resize image
     * 
     * @OA\Post(
     *      path="/api/upload-image",
     *      operationId="uploadImage",
     *      tags={"Images"},
     *      summary="Upload and resize image",
     *      description="Upload an image file, resize it, and optionally associate it with a product",
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="product_id",
     *                      type="integer",
     *                      description="Optional product ID to associate the image with"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Image uploaded successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Image uploaded successfully"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      example="/storage/images/1234567890_sample.jpg"
     *                  ),
     *                  @OA\Property(
     *                      property="path",
     *                      type="string",
     *                      example="images/1234567890_sample.jpg"
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
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return $this->ErrorResponse('Validation Error', 422, $validator->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('images', $filename, 'public');

            $imagePath = storage_path('app/public/' . $path);
            $resizedImage = Image::read($imagePath);
            $resizedImage->scale(width: 800);
            $resizedImage->save($imagePath);

            $imageUrl = Storage::url($path);

            if ($request->has('product_id')) {
                $product = \App\Models\Product::find($request->product_id);
                if ($product) {
                    $product->update(['image' => '/storage/' . $path]);
                }
            }

            return $this->SuccessResponse([
                'url' => $imageUrl,
                'path' => $path
            ], 'Image uploaded successfully');
        }

        return $this->SuccessResponse('No image provided', 400);
    }
}