<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ItemCategory;
use App\Models\UnitMaster;
use App\Exports\ItemsExport;
use App\Exports\CategoriesExport;
use Maatwebsite\Excel\Facades\Excel;


class ItemController extends Controller
{
	public function index()
	{
		$items = Item::with(['itemGroup', 'categories']) // ADD categories here
			->orderBy('name')
			->paginate(20);

		$itemGroups = ItemGroup::all();
		//dd($items->uom);
		return view('items.index', compact('items', 'itemGroups'));
	}


	public function create()
	{
		$itemGroups = ItemGroup::all();
		$seedTypes = ['Hybrid', 'Open Pollinated', 'Composite', 'Varietal', 'Parent Line'];
		$items = Item::with(['itemGroup', 'categories']) // ADD categories here
			->orderBy('name')
			->paginate(20);
		$units = UnitMaster::all();

		return view('items.create', compact('itemGroups', 'seedTypes', 'items', 'units'));
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'item_group_id' => 'required|exists:item_groups,id',
			'name' => 'required|string|max:100',
			'code' => 'required|string|max:50|unique:items',
			'remarks' => 'nullable|string|max:500',
			'uom' => 'required|string|max:20',
			'is_active' => 'nullable|boolean',
		]);
		$validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : 1;
		//dd($validated);

		$item = Item::create($validated);

		// Redirect back to the create page with items data
		return redirect()->route('items.create')
			->with('success', 'Item created successfully!');
	}

	public function show(Item $item)
	{
		$item->load(['itemGroup', 'indentItems.indent']);
		return view('items.show', compact('item'));
	}

	public function edit(Item $item)
	{
		$itemGroups = ItemGroup::all();
		$units = UnitMaster::all();
		$seedTypes = ['Hybrid', 'Open Pollinated', 'Composite', 'Varietal', 'Parent Line'];
		$items = Item::with(['itemGroup']) // ADD THIS FOR CONSISTENCY
			->orderBy('name')
			->paginate(20); // ADD THIS FOR CONSISTENCY

		return view('items.edit', compact('item', 'itemGroups', 'seedTypes', 'items', 'units'));
	}

	public function update(Request $request, Item $item)
	{
		$validated = $request->validate([
			'item_group_id' => 'required|exists:item_groups,id',
			'name' => 'required|string|max:100',
			'code' => 'required|string|max:50|unique:items,code,' . $item->id,
			'remarks' => 'nullable|string|max:500',
			'uom' => 'required|string|max:20',
			'is_active' => 'boolean',
		]);

		$item->update($validated);

		return redirect()->route('items.create')
			->with('success', 'Item updated successfully!');
	}

	public function destroy(Item $item)
	{
		if ($item->indentItems()->count() > 0) {
			return redirect()->route('items.create')
				->with('error', 'Cannot delete item with associated indent records.');
		}

		$item->delete();

		return redirect()->route('items.create')
			->with('success', 'Item deleted successfully!');
	}

	public function getItemsByGroup($groupId)
	{
		$items = Item::where('item_group_id', $groupId)
			->where('is_active', true)
			->get();

		return response()->json($items);
	}

	public function getItemCategories(Item $item)
	{
		// Eager load categories for this item
		$item->load('categories');

		return response()->json([
			'categories' => $item->categories->map(function ($category) {
				return [
					'id' => $category->id,
					'name' => $category->name,
					'is_active' => $category->is_active,
					'description' => $category->description
				];
			})->toArray()
		]);
	}

	public function updateItemCategories(Request $request, Item $item)
	{
		$request->validate([
			'categories' => 'required|array',
			'categories.*.name' => 'required|string|max:100',
			'categories.*.description' => 'nullable|string|max:500',
		]);

		// First, remove all existing categories for this item
		$item->categories()->delete();

		// Add new categories
		foreach ($request->categories as $categoryData) {
			ItemCategory::create([
				'item_id' => $item->id,
				'name' => $categoryData['name'],
				'description' => $categoryData['description']
			]);
		}

		return response()->json([
			'success' => true,
			'message' => 'Categories updated successfully!'
		]);
	}

	public function exportItems()
    {
        return Excel::download(new ItemsExport, 'items_' . date('Y-m-d') . '.xlsx');
    }

	public function exportCategories()
    {
        return Excel::download(new CategoriesExport, 'categories_' . date('Y-m-d') . '.xlsx');
    }
}
