<?php

namespace App\Http\Controllers;

use App\Models\Indent;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IndentController extends Controller
{
    public function index()
    {
        $indents = Indent::with(['requestedBy', 'items.item', 'orderByUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('indents.index', compact('indents'));
    }

    public function create()
    {
        $itemGroups = ItemGroup::with('items')->get();
        $staffMembers = Auth::user();
        $departments = Department::all();

        return view('indents.create', compact('itemGroups', 'staffMembers', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'indent_id' => 'required|exists:indents,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.required_date' => 'required|date|after:today',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        $indent = Indent::findOrFail($validated['indent_id']);

        foreach ($validated['items'] as $itemData) {
            $indent->items()->create([
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'required_date' => $itemData['required_date'],
                'remarks' => $itemData['remarks'] ?? null,
                'status' => 'pending'
            ]);
        }

        return redirect()->route('indents.show', $indent)
            ->with('success', 'Indent created successfully!');
    }
    public function show(Indent $indent)
    {

        $indent->load(['requestedBy', 'approvedBy', 'items.item', 'orderByUser', 'department']);
        return view('indents.show', compact('indent'));
    }

    public function edit(Indent $indent)
    {
        if ($indent->status !== 'draft') {
            return redirect()->route('indents.show', $indent)
                ->with('error', 'Only draft indents can be edited.');
        }

        $itemGroups = ItemGroup::with('items')->get();
        $staffMembers = Auth::user();
        $departments = Department::all();
        $indent->load('items.item');

        return view('indents.edit', compact('indent', 'itemGroups', 'staffMembers', 'departments'));
    }

    public function update(Request $request, Indent $indent)
    {

        if ($indent->status !== 'draft') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft indents can be updated.'
                ], 422);
            }
            return redirect()->route('indents.show', $indent)
                ->with('error', 'Only draft indents can be updated.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.required_date' => 'required|date|after_or_equal:indent_date',
            'items.*.remarks' => 'nullable|string|max:500',
        ]);

        try {
            //dd( $indent->items());

            // Sync items: Delete existing items and create new ones
            $indent->items()->delete();
            foreach ($validated['items'] as $itemData) {
                $indent->items()->create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'required_date' => $itemData['required_date'],
                    'remarks' => $itemData['remarks'] ?? null,
                    'status' => 'pending'
                ]);
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Indent updated successfully!',
                    'indent' => $indent->load('items')
                ]);
            }

            return redirect()->route('indents.show', $indent)
                ->with('success', 'Indent updated successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update indent: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to update indent: ' . $e->getMessage());
        }
    }



    public function destroy(Indent $indent)
    {
        if ($indent->status !== 'draft') {
            return redirect()->route('indents.index')
                ->with('error', 'Only draft indents can be deleted.');
        }

        // Delete quotation file if exists
        if ($indent->quotation_file) {
            Storage::disk('public')->delete($indent->quotation_file);
        }

        // Delete associated items
        $indent->items()->delete();

        $indent->delete();

        return redirect()->route('indents.index')
            ->with('success', 'Indent deleted successfully!');
    }

    public function submit(Indent $indent)
    {
        if ($indent->status !== 'draft') {
            return back()->with('error', 'Only draft indents can be submitted.');
        }

        $indent->update(['status' => 'submitted']);

        return back()->with('success', 'Indent submitted for approval!');
    }

    public function approve(Indent $indent)
    {
        if ($indent->status !== 'submitted') {
            return back()->with('error', 'Only submitted indents can be approved.');
        }

        $indent->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Indent approved successfully!');
    }

    public function reject(Request $request, Indent $indent)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $indent->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Indent rejected successfully!');
    }

    public function saveHeader(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:core_department,id',
            'indent_date' => 'required|date',
            'estimated_supply_date' => 'required|date|after_or_equal:indent_date',
            'order_by' => 'required|exists:users,id',
            'quotation_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'purpose' => 'required|string|max:500',
        ]);

        try {
            // Get department
            $department = Department::find($validated['department_id']);
            $deptCode = strtoupper(substr($department->department_name, 0, 2));

            // Find the highest existing number for this department
            $lastIndent = Indent::where('department_id', $validated['department_id'])
                ->where('indent_no', 'like', $deptCode . '/%')
                ->orderByRaw('CAST(SUBSTRING(indent_no, LOCATE("/", indent_no) + 1) AS UNSIGNED) DESC')
                ->first();

            $nextNumber = 1;
            if ($lastIndent && preg_match('/\/(\d+)$/', $lastIndent->indent_no, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }

            // Generate unique indent number
            $indentNo = $deptCode . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Check if this indent number already exists (safety check)
            $counter = 0;
            while (Indent::where('indent_no', $indentNo)->exists() && $counter < 100) {
                $nextNumber++;
                $indentNo = $deptCode . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                $counter++;
            }

            if ($counter >= 100) {
                throw new \Exception('Could not generate a unique indent number after 100 attempts');
            }

            // Handle file upload
            $quotationFilePath = null;
            if ($request->hasFile('quotation_file')) {
                $quotationFilePath = $request->file('quotation_file')->store('quotations', 'public');
            }

            $indent = Indent::create([
                'indent_no' => $indentNo,
                'indent_date' => $validated['indent_date'],
                'requested_by' => Auth::id(),
                'department_id' => $validated['department_id'],
                'estimated_supply_date' => $validated['estimated_supply_date'],
                'order_by' => $validated['order_by'],
                'quotation_file' => $quotationFilePath,
                'purpose' => $validated['purpose'],
                'status' => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'indent_id' => $indent->id,
                'indent_no' => $indentNo,
                'message' => 'Indent header saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving indent: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveHeaderUpdate(Request $request, Indent $indent)
    {
        if ($indent->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft indents can be updated.'
            ], 422);
        }

        $validated = $request->validate([
            'department_id' => 'required|exists:core_department,id',
            'indent_date' => 'required|date',
            'estimated_supply_date' => 'required|date|after_or_equal:indent_date',
            'order_by' => 'required|exists:users,id',
            'quotation_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'purpose' => 'required|string|max:500',
        ]);

        try {
            $headerData = [
                'department_id' => $validated['department_id'],
                'indent_date' => $validated['indent_date'],
                'estimated_supply_date' => $validated['estimated_supply_date'],
                'order_by' => $validated['order_by'],
                'purpose' => $validated['purpose'],
            ];

            if ($request->hasFile('quotation_file')) {
                if ($indent->quotation_file) {
                    Storage::disk('public')->delete($indent->quotation_file);
                }
                $headerData['quotation_file'] = $request->file('quotation_file')->store('quotations', 'public');
            }

            $indent->update($headerData);

            return response()->json([
                'success' => true,
                'indent_id' => $indent->id,
                'indent_no' => $indent->indent_no,
                'message' => 'Indent header updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating indent header: ' . $e->getMessage()
            ], 500);
        }
    }
}
