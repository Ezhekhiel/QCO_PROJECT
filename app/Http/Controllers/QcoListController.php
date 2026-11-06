<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\department;
use App\Models\qco___preparation_list;

class QcoListController extends Controller
{
    public function index() {
        $department = department::get();
        return view('qco.qco_list',['department'=>$department]);
    }
    public function main() {
        $list = qco___preparation_list::with('department')->get();

         return response()->json([
                'success' => true,
                'data' => $list
            ]);
    }
    public function store(Request $request) {
        // 1. Validasi input dari form
        $request->validate([
            'department_id' => 'required|exists:departments,id', // Validasi agar ID departemen benar-benar ada di database
            'list'          => 'required|string|max:255',
        ], [
            'list.required'          => 'Pilihan hak akses (list) wajib diisi.',
            'department_id.required' => 'Pilihan departemen wajib diisi.',
            'department_id.exists'   => 'Departemen yang dipilih tidak valid.',
        ]);

        try {
            // 2. Simpan ke database menggunakan Model Role
            // (Menyesuaikan kolom 'name' dan 'department_id' pada tabel roles)
            $role = qco___preparation_list::create([
                'list'          => $request->list,
                'department_id' => $request->department_id,
            ]);

            // 3. Kembalikan response sukses (JSON atau Redirect sesuai kebutuhan Anda)
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil ditambahkan!',
                'data'    => $role
            ]);

            // Atau jika form Anda menggunakan metode submit biasa (bukan AJAX):
            // return redirect()->back()->with('success', 'Role berhasil ditambahkan!');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan role: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $role = qco___preparation_list::findOrFail($id);
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus role: ' . $e->getMessage()
            ], 500);
        }
    }
    public function edit($id)
    {
        $list = qco___preparation_list::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $list
        ]);
    }

    // 2. Memproses update data ke database
    public function update(Request $request, $id)
    {
        $request->validate([
            'list'          => 'required|string|max:255',
            'department_id' => 'required|exists:departmentS,id',
        ], [
            'list.required'          => 'Pilihan hak akses (list) wajib diisi.',
            'department_id.required' => 'Pilihan departemen wajib diisi.',
            'department_id.exists'   => 'Departemen yang dipilih tidak valid.',
        ]);

        try {
            $list = qco___preparation_list::findOrFail($id);
            $list->update([
                'list'          => $request->list,
                'department_id' => $request->department_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'List berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui list: ' . $e->getMessage()
            ], 500);
        }
    }
}
