<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(): View
    {
        $patients = Patient::orderBy('created_at', 'desc')->get();
        return view('pages.patients.index', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Patient::create([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Patient added successfully.');
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);

        if ($patient->treatments()->exists()) {
            return back()->with('error', 'Cannot delete patient with existing treatments.');
        }

        $patient->delete();
        return back()->with('success', 'Patient deleted successfully.');
    }
}