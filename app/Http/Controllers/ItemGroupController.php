<?php

namespace App\Http\Controllers;

use App\Models\ItemGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemGroupController extends Controller
{
    public function index()
    {
        $itemGroups = ItemGroup::with(['createdBy', 'items'])->get();
        return view('item-groups.index', compact('itemGroups'));
    }

    public function create()
    {
        return view('item-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:item_groups',
            'description' => 'nullable|string|max:500',
        ]);

        $itemGroup = ItemGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('item-groups.index')
            ->with('success', 'Item group created successfully!');
    }

    public function show(ItemGroup $itemGroup)
    {
        $itemGroup->load(['createdBy', 'items']);
        return view('item-groups.show', compact('itemGroup'));
    }

    public function edit(ItemGroup $itemGroup)
    {
        return view('item-groups.edit', compact('itemGroup'));
    }

    public function update(Request $request, ItemGroup $itemGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:item_groups,name,' . $itemGroup->id,
            'description' => 'nullable|string|max:500',
        ]);

        $itemGroup->update($validated);

        return redirect()->route('item-groups.index')
            ->with('success', 'Item group updated successfully!');
    }

    public function destroy(ItemGroup $itemGroup)
    {
        if ($itemGroup->items()->count() > 0) {
            return redirect()->route('item-groups.index')
                ->with('error', 'Cannot delete item group with associated items.');
        }

        $itemGroup->delete();

        return redirect()->route('item-groups.index')
            ->with('success', 'Item group deleted successfully!');
    }
}
