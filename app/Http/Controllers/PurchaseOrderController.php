<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\Indent;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['indent', 'vendor', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $indents = Indent::where('status', 'approved')
            ->doesntHave('purchaseOrder')
            ->with(['department', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('Indents loaded for PO index', ['count' => $indents->count(), 'indent_ids' => $indents->pluck('id')->toArray()]);

        $vendors = Vendor::where('is_active', 1)
            ->where('approval_status', 'approved')
            ->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'indents', 'vendors'));
    }

    public function draft(Request $request)
    {
        $validated = $request->validate([
            'indent_id' => 'required|exists:indents,id',
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:po_date',
            'terms' => 'nullable|string|max:500',
        ]);

        if (PurchaseOrder::where('indent_id', $validated['indent_id'])->exists()) {
            return response()->json(['error' => 'A Purchase Order already exists for this indent.'], 400);
        }

        try {
            DB::beginTransaction();

            $lastPo = PurchaseOrder::orderBy('id', 'desc')->first();
            $nextNumber = $lastPo ? intval(substr($lastPo->po_no, strpos($lastPo->po_no, '/') + 1)) + 1 : 1;
            $poNo = 'PO/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_no' => $poNo,
                'indent_id' => $validated['indent_id'],
                'vendor_id' => $validated['vendor_id'],
                'po_date' => $validated['po_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'total_amount' => 0,
                'terms' => $validated['terms'],
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            \Log::info('Draft PO created', ['po_id' => $po->id]);

            return response()->json(['po_id' => $po->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to save draft PO', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save draft PO: ' . $e->getMessage()], 500);
        }
    }

    public function getIndentItems(Request $request)
    {
        \Log::info('getIndentItems called', ['indent_id' => $request->query('indent_id')]);

        $indentId = $request->query('indent_id');

        if (!$indentId) {
            \Log::error('Indent ID is missing');
            return response()->json(['error' => 'Indent ID is required'], 400);
        }

        $indent = Indent::where('id', $indentId)->where('status', 'approved')->first();

        if (!$indent) {
            \Log::error('Indent not found or not approved', ['indent_id' => $indentId]);
            return response()->json(['error' => 'Invalid or unapproved indent'], 404);
        }

        if (PurchaseOrder::where('indent_id', $indentId)->exists()) {
            \Log::warning('Purchase Order already exists for indent', ['indent_id' => $indentId]);
            return response()->json(['error' => 'A Purchase Order already exists for this indent'], 400);
        }

        $approvedItems = $indent->items()
            ->where('status', 'approved')
            ->with('item')
            ->get();

        if ($approvedItems->isEmpty()) {
            \Log::warning('No approved items found for indent', ['indent_id' => $indentId]);
            return response()->json(['error' => 'No approved items found for this indent'], 404);
        }

        $items = $approvedItems->map(function ($item) {
            return [
                'id' => $item->id,
                'item_id' => $item->item_id,
                'item_name' => $item->item ? $item->item->name : 'N/A',
                'quantity_approve' => $item->quantity_approve,
                'required_date' => $item->required_date ? $item->required_date->format('Y-m-d') : null,
            ];
        });

        \Log::info('Returning items for indent', ['indent_id' => $indentId, 'items' => $items->toArray()]);
        return response()->json(['items' => $items]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        \Log::info('Editing PO', ['po_id' => $purchaseOrder->id, 'status' => $purchaseOrder->status]);

        if (!in_array($purchaseOrder->status, ['draft', 'issued'])) {
            \Log::error('PO edit failed: Invalid status', ['po_id' => $purchaseOrder->id, 'status' => $purchaseOrder->status]);
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('error', 'Only draft or issued POs can be edited.');
        }

        $indents = Indent::where('status', 'approved')
            ->doesntHave('purchaseOrder')
            ->with(['department', 'requestedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $vendors = Vendor::where('is_active', 1)
            ->where('approval_status', 'approved')
            ->get();

        $indentItems = $purchaseOrder->status === 'draft'
            ? $purchaseOrder->indent->items()->where('status', 'approved')->with('item')->get()
            : $purchaseOrder->items()->with('item')->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'indents', 'vendors', 'indentItems'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        \Log::info('Updating PO', ['po_id' => $purchaseOrder->id, 'request_data' => $request->all()]);

        if (!in_array($purchaseOrder->status, ['draft', 'issued'])) {
            \Log::error('PO update failed: Invalid status', ['po_id' => $purchaseOrder->id, 'status' => $purchaseOrder->status]);
            return back()->with('error', 'Only draft or issued POs can be updated.');
        }

        try {
            $validated = $request->validate([
                'indent_id' => 'required|exists:indents,id',
                'vendor_id' => 'required|exists:vendors,id',
                'po_date' => 'required|date',
                'expected_delivery_date' => 'required|date|after_or_equal:po_date',
                'terms' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.indent_item_id' => 'required|exists:indent_items,id',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0.01',
                'items.*.required_date' => 'required|date',
                'items.*.remarks' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for PO update', ['po_id' => $purchaseOrder->id, 'errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $purchaseOrder->update([
                'vendor_id' => $validated['vendor_id'],
                'po_date' => $validated['po_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'terms' => $validated['terms'],
                'total_amount' => $totalAmount,
                'status' => 'issued',
            ]);

            $purchaseOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                PoItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'indent_item_id' => $item['indent_item_id'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'required_date' => $item['required_date'],
                    'remarks' => $item['remarks'],
                ]);
            }

            DB::commit();
            \Log::info('PO updated successfully', ['po_id' => $purchaseOrder->id]);

            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'PO updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update PO', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update PO: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        \Log::info('Attempting to delete PO', ['po_id' => $purchaseOrder->id, 'status' => $purchaseOrder->status]);

        if ($purchaseOrder->status !== 'draft') {
            \Log::error('PO deletion failed: Not in draft status', ['po_id' => $purchaseOrder->id, 'status' => $purchaseOrder->status]);
            return back()->with('error', 'Only draft POs can be deleted.');
        }

        try {
            DB::beginTransaction();
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
            DB::commit();
            \Log::info('PO deleted successfully', ['po_id' => $purchaseOrder->id]);
            return redirect()->route('purchase-orders.index')->with('success', 'PO deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete PO', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete PO: ' . $e->getMessage());
        }
    }

      public function store(Request $request)
    {
        $validated = $request->validate([
            'indent_id' => 'required|exists:indents,id',
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:po_date',
            'terms' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.indent_item_id' => 'required|exists:indent_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.required_date' => 'required|date',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        if (PurchaseOrder::where('indent_id', $validated['indent_id'])->exists()) {
            return back()->with('error', 'A Purchase Order already exists for this indent.');
        }

        try {
            DB::beginTransaction();

            // Generate PO number
            $lastPo = PurchaseOrder::orderBy('id', 'desc')->first();
            $nextNumber = $lastPo ? intval(substr($lastPo->po_no, strpos($lastPo->po_no, '/') + 1)) + 1 : 1;
            $poNo = 'PO/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $po = PurchaseOrder::create([
                'po_no' => $poNo,
                'indent_id' => $validated['indent_id'],
                'vendor_id' => $validated['vendor_id'],
                'po_date' => $validated['po_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'total_amount' => $totalAmount,
                'terms' => $validated['terms'],
                'status' => 'issued',
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                PoItem::create([
                    'purchase_order_id' => $po->id,
                    'indent_item_id' => $item['indent_item_id'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'required_date' => $item['required_date'],
                    'remarks' => $item['remarks'],
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-orders.show', $po)->with('success', 'PO created and issued successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create PO: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['indent', 'vendor', 'items.item', 'createdBy']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }
}