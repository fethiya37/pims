<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryTransferController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $transfers = InventoryTransfer::with(['fromLocation', 'toLocation', 'items.product', 'requestedBy', 'approvedBy', 'issuedBy', 'receivedBy'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::orderBy('id', 'desc')->get();
        } else {
            $transfers = InventoryTransfer::where('from_location_id', $user->location_id)
                ->orWhere('to_location_id', $user->location_id)
                ->with(['fromLocation', 'toLocation', 'items.product', 'requestedBy', 'approvedBy', 'issuedBy', 'receivedBy'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('id', $user->location_id)->get();
        }

        $products = Product::all();
        $stores = Location::where('type', 'store')->get();
        $pointOfUseStores = Location::where('type', 'point_of_use')->get();

        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        return view('pages.inventory_transfers.index', compact('transfers', 'locations', 'products', 'stores', 'pointOfUseStores', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'collected_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        $fromLocation = Location::find($request->from_location_id);
        $toLocation = Location::find($request->to_location_id);

        if (!$fromLocation || $fromLocation->type !== 'store') {
            return back()->with('error', 'From location must be a Store.');
        }

        if (!$toLocation || $toLocation->type !== 'point_of_use') {
            return back()->with('error', 'To location must be a Point of Use.');
        }

        if ($user->role->role_name !== 'Super Admin') {
            if ($request->to_location_id != $user->location_id) {
                return back()->with('error', 'You can only request transfers to your assigned location.');
            }
        }

        DB::beginTransaction();

        try {
            $totalErrors = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $requestedQty = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                $availableQty = StockBatch::where('product_id', $item['product_id'])
                    ->where('location_id', $request->from_location_id)
                    ->sum('quantity');

                if ($availableQty < $requestedQty) {
                    $totalErrors[] = "Not enough stock for {$product->name}. Available: {$availableQty}, Requested: {$requestedQty}.";
                }
            }

            if (!empty($totalErrors)) {
                return back()->withErrors($totalErrors)->withInput();
            }

            $transfer = InventoryTransfer::create([
                'from_location_id' => $request->from_location_id,
                'to_location_id' => $request->to_location_id,
                'requested_by' => auth()->id(),
                'requested_date' => now(),
                'status' => 'pending',
                'remarks' => $request->remarks,
                'collected_by' => $request->collected_by,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $totalUnits,
                ]);
            }

            DB::commit();

            return redirect()->route('inventory-transfers.index')
                ->with('success', 'Transfer request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $transfer = InventoryTransfer::with('items')->findOrFail($id);

        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be edited.');
        }

        $user = Auth::user();

        $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'collected_by' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        $fromLocation = Location::find($request->from_location_id);
        $toLocation = Location::find($request->to_location_id);

        if (!$fromLocation || $fromLocation->type !== 'store') {
            return back()->with('error', 'From location must be a Store.');
        }

        if (!$toLocation || $toLocation->type !== 'point_of_use') {
            return back()->with('error', 'To location must be a Point of Use.');
        }

        if ($user->role->role_name !== 'Super Admin') {
            if ($request->to_location_id != $user->location_id) {
                return back()->with('error', 'You can only request transfers to your assigned location.');
            }
        }

        DB::beginTransaction();

        try {
            $totalErrors = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $requestedQty = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                $availableQty = StockBatch::where('product_id', $item['product_id'])
                    ->where('location_id', $request->from_location_id)
                    ->sum('quantity');

                if ($availableQty < $requestedQty) {
                    $totalErrors[] = "Not enough stock for {$product->name}. Available: {$availableQty}, Requested: {$requestedQty}.";
                }
            }

            if (!empty($totalErrors)) {
                return back()->withErrors($totalErrors)->withInput();
            }

            $transfer->update([
                'from_location_id' => $request->from_location_id,
                'to_location_id' => $request->to_location_id,
                'remarks' => $request->remarks,
                'collected_by' => $request->collected_by,
            ]);

            $transfer->items()->delete();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $totalUnits,
                ]);
            }

            DB::commit();

            return redirect()->route('inventory-transfers.index')
                ->with('success', 'Transfer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating transfer: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $transfer = InventoryTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be approved.');
        }

        $transfer->update([
            'approved_by' => auth()->id(),
            'approved_date' => now(),
            'status' => 'approved',
        ]);

        return back()->with('success', 'Transfer approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $transfer = InventoryTransfer::findOrFail($id);

        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be rejected.');
        }

        $transfer->update([
            'approved_by' => auth()->id(),
            'approved_date' => now(),
            'status' => 'rejected',
            'remarks' => $request->remarks ?? $transfer->remarks,
        ]);

        return back()->with('success', 'Transfer rejected.');
    }

    public function issue($id)
    {
        $transfer = InventoryTransfer::with('items.product')->findOrFail($id);

        if ($transfer->status !== 'approved') {
            return back()->with('error', 'Only approved transfers can be issued.');
        }

        DB::beginTransaction();

        try {
            $errors = [];

            foreach ($transfer->items as $item) {
                $qtyToIssue = $item->quantity;

                $batches = StockBatch::where('product_id', $item->product_id)
                    ->where('location_id', $transfer->from_location_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToIssue <= 0) break;

                    $takeQty = min($qtyToIssue, $batch->quantity);

                    $batch->decrement('quantity', $takeQty);

                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'from_location_id' => $transfer->from_location_id,
                        'to_location_id' => $transfer->to_location_id,
                        'transaction_type' => 'transfer',
                        'reference' => 'TRF-' . $transfer->id,
                        'lot_number' => $batch->lot_number,
                        'expiry_date' => $batch->expiry_date,
                        'quantity' => $takeQty,
                        'user_id' => auth()->id(),
                        'notes' => 'Transfer from ' . $transfer->fromLocation->name . ' to ' . $transfer->toLocation->name,
                    ]);

                    $qtyToIssue -= $takeQty;
                }

                if ($qtyToIssue > 0) {
                    $errors[] = "Not enough stock for {$item->product->name}. Short by {$qtyToIssue} units.";
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors($errors);
            }

            $transfer->update([
                'issued_by' => auth()->id(),
                'issued_date' => now(),
                'status' => 'issued',
            ]);

            DB::commit();

            return back()->with('success', 'Transfer issued successfully. Stock deducted from Store.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error issuing transfer: ' . $e->getMessage());
        }
    }

    public function receive($id)
    {
        $transfer = InventoryTransfer::findOrFail($id);

        if ($transfer->status !== 'issued') {
            return back()->with('error', 'Only issued transfers can be received.');
        }

        DB::beginTransaction();

        try {
            $transactions = InventoryTransaction::where('reference', 'TRF-' . $transfer->id)
                ->where('from_location_id', $transfer->from_location_id)
                ->where('to_location_id', $transfer->to_location_id)
                ->where('transaction_type', 'transfer')
                ->get();

            foreach ($transactions as $txn) {
                $stockBatch = StockBatch::where('product_id', $txn->product_id)
                    ->where('location_id', $transfer->to_location_id)
                    ->where('lot_number', $txn->lot_number)
                    ->where('expiry_date', $txn->expiry_date)
                    ->first();

                if ($stockBatch) {
                    $stockBatch->increment('quantity', $txn->quantity);
                } else {
                    StockBatch::create([
                        'product_id' => $txn->product_id,
                        'location_id' => $transfer->to_location_id,
                        'lot_number' => $txn->lot_number,
                        'expiry_date' => $txn->expiry_date,
                        'quantity' => $txn->quantity,
                    ]);
                }

                InventoryTransaction::create([
                    'product_id' => $txn->product_id,
                    'from_location_id' => null,
                    'to_location_id' => $transfer->to_location_id,
                    'transaction_type' => 'receiving',
                    'reference' => 'TRF-' . $transfer->id,
                    'lot_number' => $txn->lot_number,
                    'expiry_date' => $txn->expiry_date,
                    'quantity' => $txn->quantity,
                    'user_id' => auth()->id(),
                    'notes' => 'Received from ' . $transfer->fromLocation->name . ' via transfer',
                ]);
            }

            $transfer->update([
                'received_by' => auth()->id(),
                'received_date' => now(),
                'status' => 'received',
            ]);

            DB::commit();

            return back()->with('success', 'Transfer received successfully. Stock added to Point of Use.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error receiving transfer: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $transfer = InventoryTransfer::findOrFail($id);

        if (!in_array($transfer->status, ['pending', 'rejected'])) {
            return back()->with('error', 'Only pending or rejected transfers can be deleted.');
        }

        $transfer->items()->delete();
        $transfer->delete();

        return back()->with('success', 'Transfer deleted successfully.');
    }
}