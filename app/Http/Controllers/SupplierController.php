<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierContact;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('contacts')->get();
        return view('pages.suppliers.index', compact('suppliers'));
    }

    public function show($id)
    {
        $supplier = Supplier::with('contacts')->findOrFail($id);
        return response()->json($supplier);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.qualification' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        foreach ($request->contacts as $contact) {
            SupplierContact::create([
                'supplier_id' => $supplier->id,
                'name' => $contact['name'],
                'phone' => $contact['phone'] ?? null,
                'qualification' => $contact['qualification'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contacts' => 'required|array|min:1',
            'contacts.*.id' => 'nullable|exists:supplier_contacts,id',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.qualification' => 'nullable|string|max:255',
        ]);

        $supplier->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $existingIds = [];
        foreach ($request->contacts as $contactData) {
            if (isset($contactData['id']) && $contactData['id']) {
                $contact = SupplierContact::find($contactData['id']);
                if ($contact && $contact->supplier_id == $supplier->id) {
                    $contact->update([
                        'name' => $contactData['name'],
                        'phone' => $contactData['phone'] ?? null,
                        'qualification' => $contactData['qualification'] ?? null,
                    ]);
                    $existingIds[] = $contact->id;
                    continue;
                }
            }
            // Create new contact
            $newContact = SupplierContact::create([
                'supplier_id' => $supplier->id,
                'name' => $contactData['name'],
                'phone' => $contactData['phone'] ?? null,
                'qualification' => $contactData['qualification'] ?? null,
            ]);
            $existingIds[] = $newContact->id;
        }

        // Delete contacts that are no longer in the request
        $supplier->contacts()->whereNotIn('id', $existingIds)->delete();

        return redirect()->back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->contacts()->delete();
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}