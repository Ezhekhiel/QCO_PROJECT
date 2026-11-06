<?php

namespace App\Http\Controllers;

use App\Models\cell_info;
use Illuminate\Http\Request;

class CellInfoController extends Controller
{
    public function index()
    {
        return view('cell_info.index'); // Tampilan Blade utama
    }

    public function main()
    {
        $data = cell_info::orderby('area')->get();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'building' => 'required|string|max:255',
            'area'     => 'required|string|max:255',
            'eol'      => 'nullable|integer',
            'mp'       => 'nullable|integer',
        ]);

        cell_info::create([
            'building' => $request->building,
            'area'     => $request->area,
            'eol'      => $request->eol ?? 0,
            'mp'       => $request->mp ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Cell Info berhasil ditambahkan!'
        ]);
    }

    public function edit($id)
    {
        $cell = cell_info::find($id);
        if (!$cell) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cell
        ]);
    }

    public function update(Request $request, $id)
    {
        $cell = cell_info::find($id);
        if (!$cell) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'building' => 'required|string|max:255',
            'area'     => 'required|string|max:255',
            'eol'      => 'nullable|integer',
            'mp'       => 'nullable|integer',
        ]);

        $cell->update([
            'building' => $request->building,
            'area'     => $request->area,
            'eol'      => $request->eol ?? 0,
            'mp'       => $request->mp ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Cell Info berhasil diperbarui!'
        ]);
    }

    public function destroy($id)
    {
        $cell = cell_info::find($id);
        if (!$cell) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $cell->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Cell Info berhasil dihapus!'
        ]);
    }
}
