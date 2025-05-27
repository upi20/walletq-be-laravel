<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AccountCategoryController extends Controller
{
    // Menampilkan semua kategori akun untuk user
    public function index(Request $request)
    {
        $accountCategories = $request->user()->accountCategories; // Mengambil kategori akun milik user
        return response()->json([
            'status' => 200,
            'message' => 'Account categories fetched successfully',
            'data' => $accountCategories
        ], 200);
    }

    // Menambahkan kategori akun baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:account_categories,name',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Menyimpan kategori akun untuk user
        $accountCategory = $request->user()->accountCategories()->create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Account category created successfully',
            'data' => $accountCategory
        ], 201);
    }

    // Menampilkan kategori akun tertentu
    public function show(Request $request, $id)
    {
        $accountCategory = $request->user()->accountCategories()->find($id);

        if (!$accountCategory) {
            return response()->json([
                'status' => 404,
                'message' => 'Account category not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Account category fetched successfully',
            'data' => $accountCategory
        ], 200);
    }

    // Mengupdate kategori akun
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $accountCategory = $request->user()->accountCategories()->find($id);

        if (!$accountCategory) {
            return response()->json([
                'status' => 404,
                'message' => 'Account category not found'
            ], 404);
        }

        // Mengupdate kategori akun
        $accountCategory->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Account category updated successfully',
            'data' => $accountCategory
        ], 200);
    }

    // Menghapus kategori akun
    public function destroy(Request $request, $id)
    {
        $accountCategory = $request->user()->accountCategories()->find($id);

        if (!$accountCategory) {
            return response()->json([
                'status' => 404,
                'message' => 'Account category not found'
            ], 404);
        }

        // Menghapus kategori akun
        $accountCategory->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Account category deleted successfully'
        ], 200);
    }
}
