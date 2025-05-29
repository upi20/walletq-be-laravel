<?php

namespace App\Http\Controllers\API\User\MasterData;

use App\Http\Controllers\Controller;
use App\Models\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountCategoryController extends Controller
{    public function index(Request $request)
    {
        $query = $request->user()->accountCategories()
            ->orderBy('name', 'asc');

        // Apply search filter if provided        // Build query with filters
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('type', 'like', $searchTerm);
            });
        }

        // Apply type filter if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Paginate results
        $perPage = $request->input('per_page', 10);
        $categories = $query->paginate($perPage);

        return response()->json([
            'status' => 200,
            'message' => 'Account categories fetched successfully',
            'data' => [
                'categories' => $categories->items(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'last_page' => $categories->lastPage()
                ]
            ]
        ]);
	}    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [            'name' => [
                'required',
                'string',
                'max:255',
                function($attribute, $value, $fail) use ($request) {
                    $exists = AccountCategory::where('user_id', $request->user()->id)
                        ->where('name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('You already have a category with this name.');
                    }
                }
            ],
            'type' => 'required|in:cash,bank,e-wallet,digital,credit_card,investment,others',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {            DB::beginTransaction();

            $category = $request->user()->accountCategories()->create([
                'name' => $request->name,
                'type' => $request->type,
                'is_default' => false
            ]);

            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Account category created successfully',
                'data' => $category
            ], 201);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json([
				'status' => 500,
				'message' => 'Error creating account category',
				'error' => $e->getMessage()
			], 500);
		}
	}
	public function show(Request $request, $id)
	{
		$category = $request->user()->accountCategories()->find($id);

		if (!$category) {
			return response()->json([
				'status' => 404,
				'message' => 'Account category not found'
			], 404);
		}

		return response()->json([
			'status' => 200,
			'message' => 'Account category fetched successfully',
			'data' => $category
		]);
	}    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [            'name' => [
                'required',
                'string',
                'max:255',
                function($attribute, $value, $fail) use ($request, $id) {
                    $exists = AccountCategory::where('user_id', $request->user()->id)
                        ->where('name', $value)
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($exists) {
                        $fail('You already have a category with this name.');
                    }
                }
            ],
            'type' => 'required|in:cash,bank,e-wallet,digital,credit_card,investment,others',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
			$category = $request->user()->accountCategories()->find($id);

			if (!$category) {
				return response()->json([
					'status' => 404,
					'message' => 'Account category not found'
				], 404);
			}

			if ($category->is_default) {
				return response()->json([
					'status' => 422,
					'message' => 'Cannot modify default category'
				], 422);
			}

			$category->update([
				'name' => $request->name,
				'type' => $request->type
			]);			return response()->json([
				'status' => 200,
				'message' => 'Account category updated successfully',
				'data' => $category
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => 500,
				'message' => 'Error updating account category',
				'error' => $e->getMessage()
			], 500);
		}
	}
    public function destroy(Request $request, $id)
    {
        try {
            $category = $request->user()->accountCategories()->find($id);

            if (!$category) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Account category not found'
                ], 404);
            }

            if ($category->is_default) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Cannot delete default category'
                ], 422);
            }

            // Check if category has associated accounts
            if ($category->accounts()->count() > 0) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Cannot delete category with associated accounts'
                ], 422);
            }

            $category->delete();            return response()->json([
                'status' => 200,
                'message' => 'Account category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error deleting account category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
