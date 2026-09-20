<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Tampilkan semua kategori (opsional filter berdasarkan tipe: income/expense)
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $categories = $query->latest()->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar kategori berhasil diambil',
            'data'    => $categories
        ], 200);
    }

    /**
     * Tambah kategori baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori berhasil ditambahkan',
            'data'    => $category
        ], 201);
    }

    /**
     * Tampilkan detail kategori tunggal
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail kategori berhasil diambil',
            'data'    => $category
        ], 200);
    }

    /**
     * Perbarui data kategori
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:income,expense',
        ]);

        $category->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori berhasil diperbarui',
            'data'    => $category
        ], 200);
    }

    /**
     * Hapus kategori
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        // Cek jika kategori masih digunakan dalam transaksi
        if ($category->transactions()->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan pada transaksi'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}