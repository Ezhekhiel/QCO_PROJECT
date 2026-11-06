<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\department;

class departmentController extends Controller
{
    public function index() {
        return view('auth.department');
    }
    public function main(Request $request) {
        if ($request->ajax()) {
            $departments = department::get(); // Opsional: menghitung jumlah karyawan per departemen

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        }
    }
    public function import_department(Request $request) {
        $request->validate([
            'department' => 'required|string|max:255|unique:departments,nama',
        ], [
            'department.required' => 'Nama departemen wajib diisi.',
            'department.unique' => 'Nama departemen sudah ada.'
        ]);

        try {
            // Simpan data ke database
            $department = department::firstOrCreate([
                'nama' => $request->department
            ]);

            if (!$department->wasRecentlyCreated) {
                return response()->json([
                    'success' => false,
                    'message' =>  'Department tersebut sudah ada!'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Departemen berhasil ditambahkan!',
                'data' => $department
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
    // 1. Mengambil data departemen untuk modal edit
    public function edit($id)
    {
        $department = department::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $department
        ]);
    }

    // 2. Memproses update data departemen
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:departments,nama,' . $id,
        ], [
            'nama.required' => 'Nama departemen wajib diisi.',
            'nama.unique'   => 'Nama departemen sudah ada.',
        ]);

        try {
            $department = department::findOrFail($id);
            $department->update([
                'nama' => $request->nama
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Departemen berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui departemen: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. Menghapus data departemen
    public function destroy($id)
    {
        try {
            $department = department::findOrFail($id);
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Departemen berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus departemen: ' . $e->getMessage()
            ], 500);
        }
    }
}
