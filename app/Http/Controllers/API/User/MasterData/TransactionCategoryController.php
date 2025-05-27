<?php

namespace App\Http\Controllers\API\User\MasterData;

use App\Http\Controllers\Controller;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TransactionCategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type'); // filter by type if provided
        $query = $request->user()->transactionCategories()
            ->where('is_hide', false)
            ->orderBy('name', 'asc');
            
        if ($type) {
            $query->where('type', $type);
        }

        $categories = $query->get();

        return response()->json([
            'status' => 200,
            'message' => 'Transaction categories fetched successfully',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $category = $request->user()->transactionCategories()->create([
                'name' => $request->name,
                'type' => $request->type,
                'is_default' => false,
                'is_hide' => false
            ]);

            DB::commit();

            return response()->json([
                'status' => 201,
                'message' => 'Transaction category created successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error creating transaction category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $category = $request->user()->transactionCategories()->find($id);

        if (!$category) {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction category not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Transaction category fetched successfully',
            'data' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $category = $request->user()->transactionCategories()->find($id);

            if (!$category) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Transaction category not found'
                ], 404);
            }

            if ($category->is_default) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Cannot update default transaction category'
                ], 422);
            }

            DB::beginTransaction();

            $category->update([
                'name' => $request->name,
                'type' => $request->type
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Transaction category updated successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error updating transaction category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $category = $request->user()->transactionCategories()->find($id);

            if (!$category) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Transaction category not found'
                ], 404);
            }

            if ($category->is_default) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Cannot delete default transaction category'
                ], 422);
            }

            // Check if category is being used in any transactions
            if ($category->transactions()->exists()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Cannot delete category that is being used in transactions'
                ], 422);
            }

            DB::beginTransaction();

            $category->delete();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Transaction category deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Error deleting transaction category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
