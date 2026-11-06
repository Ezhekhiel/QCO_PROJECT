<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanMaster;
use App\Imports\PlanMasterImport;
use Maatwebsite\Excel\Facades\Excel;

class PlanMasterController extends Controller
{
    public function index()
    {
        $plans = PlanMaster::orderBy('id', 'desc')->paginate(50);
        return view('plan_master.index', compact('plans'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            Excel::import(new PlanMasterImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Plan Master berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
