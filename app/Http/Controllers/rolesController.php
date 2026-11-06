<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\department;
use App\Models\Role;

class rolesController extends Controller
{
    public function index() {
        $department = department::get();
        return view('auth.roles',['department'=>$department]);
    }
    public function main() {
        $role = Role::with('department')->get();
         return response()->json([
                'success' => true,
                'data' => $role
            ]);
    }
    public function store(Request $request) {
        // 1. Validasi input dari form
        $request->validate([
            'role'          => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id', // Validasi agar ID departemen benar-benar ada di database
        ], [
            'role.required'          => 'Pilihan hak akses (role) wajib diisi.',
            'department_id.required' => 'Pilihan departemen wajib diisi.',
            'department_id.exists'   => 'Departemen yang dipilih tidak valid.',
        ]);

        try {
            $role = Role::firstOrCreate([
                'name'          => $request->role,
                'department_id' => $request->department_id,
            ]);

            if (!$role->wasRecentlyCreated) {
                return response()->json([
                    'success' => false,
                    'message' =>  'Role tersebut sudah ada!'
                ], 500);
            }

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
            $role = Role::findOrFail($id);
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
        $role = Role::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    // 2. Memproses update data ke database
    public function update(Request $request, $id)
    {
        $request->validate([
            'role'          => 'required|string|max:255',
            'department_id' => 'required|exists:department,id',
        ], [
            'role.required'          => 'Pilihan hak akses (role) wajib diisi.',
            'department_id.required' => 'Pilihan departemen wajib diisi.',
            'department_id.exists'   => 'Departemen yang dipilih tidak valid.',
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->update([
                'name'          => $request->role,
                'department_id' => $request->department_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui role: ' . $e->getMessage()
            ], 500);
        }
    }
}
