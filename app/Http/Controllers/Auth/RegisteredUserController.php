<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\department;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $department = department::get();
        return view('auth.register',['department'=>$department]);
    }
    public function main()
    {
        $users = User::with(['department', 'role'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
   // Menyimpan user baru via AJAX
    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'nik'           => ['required', 'string', 'max:50', 'unique:users,nik'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required'],
            'department_id' => ['required', 'exists:departments,id'],
            'role_id'       => ['required', 'exists:roles,id'],
        ], [
            'name.required'          => 'Nama wajib diisi.',
            'nik.required'           => 'NIK wajib diisi.',
            'nik.unique'             => 'NIK sudah terdaftar.',
            'email.required'         => 'Email wajib diisi.',
            'email.unique'           => 'Email sudah terdaftar.',
            'password.required'      => 'Password wajib diisi.',
            'department_id.required' => 'Departemen wajib dipilih.',
            'role_id.required'       => 'Role wajib dipilih.',
        ]);

        try {
            $user = User::create([
                'name'          => $request->name,
                'nik'           => $request->nik,
                'email'         => $request->email,
                'password'      => Hash::make($request->password), // Hash password standar Laravel
                'department_id' => $request->department_id,
                'role_id'       => $request->role_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan!',
                'data'    => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan user: ' . $e->getMessage()
            ], 500);
        }
    }
    // Menghapus user
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    // Opsional: API untuk mengambil role berdasarkan department yang dipilih
    public function getRolesByDepartment(Request $request)
    {
        // Mengambil data dari query string (?department_id=...)
        $departmentId = $request->input('department_id');

        $roles = Role::where('department_id', $departmentId)->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }
}
