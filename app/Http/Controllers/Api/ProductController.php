<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Activity $activity): JsonResponse
    {
        $products = $activity->products()->with('createdBy')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function store(Request $request, Activity $activity): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:documento,codigo,diseno,testcase,configuracion',
            'url_or_path' => 'nullable|string|max:500',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $data['activity_id'] = $activity->id;
        $data['created_by'] = optional(auth()->user())->id ?? $data['created_by'];

        $product = $activity->products()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => $product->load('createdBy'),
        ], 201);
    }

    public function show(Activity $activity, Product $product): JsonResponse
    {
        if ($product->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product->load('createdBy'),
        ]);
    }

    public function update(Request $request, Activity $activity, Product $product): JsonResponse
    {
        if ($product->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:documento,codigo,diseno,testcase,configuracion',
            'url_or_path' => 'nullable|string|max:500',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $product->update($data);

        return response()->json([
            'success' => true,
            'data' => $product->fresh(),
        ]);
    }

    public function destroy(Activity $activity, Product $product): JsonResponse
    {
        if ($product->activity_id !== $activity->id) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }
}
