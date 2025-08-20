@extends('layouts.app')

@section('content')
<style>
	.category-row {
		background-color: #f8f9fa;
	}

	.category-badge {
		font-size: 0.75rem;
		margin-right: 5px;
		margin-bottom: 3px;
		display: inline-block;
	}

	.action-buttons {
		white-space: nowrap;
	}

	.category-container {
		padding: 15px;
		border-radius: 5px;
		margin-bottom: 10px;
	}

	.readonly-input {
		background-color: #f8f9fa;
		cursor: not-allowed;
	}
</style>
<div class="container-fluid py-4">
	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5>Item Master</h5>
					<span class="badge bg-primary">{{ $items->total() }} Items</span>
				</div>
				<div class="card-body">
					@if(session('success'))
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						{{ session('success') }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
					@endif
					@if(session('error'))
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						{{ session('error') }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
					@endif

					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover">
							<thead class="thead-dark">
								<tr>
									<th>Code</th>
									<th>Name</th>
									<th>Group</th>
									<th>UOM</th>
									<th>Status</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@forelse($items as $item)
								<tr class="item-row" data-item-id="{{ $item->id }}">
									<td>{{ $item->code }}</td>
									<td>{{ $item->name }}</td>
									<td>{{ $item->itemGroup->name }}</td>
									<td>{{ $item->uom }}</td>
									<td>
										<span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">
											{{ $item->is_active ? 'Active' : 'Inactive' }}
										</span>
									</td>
									<td class="action-buttons">
										<div class="d-flex align-items-center">
											<!-- View button -->
											{{-- <a href="{{ route('items.show', $item) }}" class="btn btn-sm btn-link text-info p-0 me-2" title="View">
											<i class="ri-eye-fill align-bottom" style="font-size: 1.2rem;"></i>
											</a> --}}

											<!-- Edit button -->
											<a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-link text-primary p-0 me-2" title="Edit">
												<i class="ri-pencil-fill align-bottom" style="font-size: 1.2rem;"></i>
											</a>

											<!-- Delete button -->
											<form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
												@csrf
												@method('DELETE')
												<button type="submit" class="btn btn-sm btn-link text-danger p-0 me-2"
													onclick="return confirm('Are you sure you want to delete this item?')"
													title="Delete">
													<i class="ri-delete-bin-line align-bottom" style="font-size: 1.2rem;"></i>
												</button>
											</form>

											<!-- Add Category button -->
											<button type="button" class="btn btn-sm btn-link text-success p-0 me-2"
												title="Add Category" data-bs-toggle="modal" data-bs-target="#addCategoryModal"
												onclick="setItemForCategory({{ $item->id }}, '{{ $item->name }}', '{{ $item->code }}')">
												<i class="ri-add-box-fill align-bottom" style="font-size: 1.2rem;"></i>
											</button>

											<!-- Show Categories button -->
											<button type="button" class="btn btn-sm btn-link text-warning p-0 toggle-categories"
												title="Show Categories" data-bs-toggle="modal" data-bs-target="#viewCategoriesModal"
												onclick="viewCategories({{ $item->id }}, '{{ $item->name }}', '{{ $item->code }}')">
												<i class="ri-list-check align-bottom" style="font-size: 1.2rem;"></i>
											</button>
										</div>
									</td>
								</tr>

								@empty
								<tr>
									<td colspan="7" class="text-center">No items found. Create your first item!</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>

					@if($items->hasPages())
					<div class="d-flex justify-content-center mt-3">
						{{ $items->links() }}
					</div>
					@endif
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="card">
				<div class="card-header">
					<h5>Create New Item</h5>
				</div>
				<div class="card-body">
					<form action="{{ route('items.store') }}" method="POST">
						@csrf

						<div class="row">
							<div class="col-md-12">
								<div class="form-group mb-3">
									<label for="item_group_id" class="form-label">Item Group *</label>
									<select class="form-control form-control-sm @error('item_group_id') is-invalid @enderror"
										id="item_group_id" name="item_group_id" required>
										<option value="">Select Group</option>
										@foreach($itemGroups as $group)
										<option value="{{ $group->id }}" {{ old('item_group_id') == $group->id ? 'selected' : '' }}>
											{{ $group->name }}
										</option>
										@endforeach
									</select>
									@error('item_group_id')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>

						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group mb-3">
									<label for="name" class="form-label">Item Name *</label>
									<input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
										id="name" name="name" value="{{ old('name') }}" required>
									@error('name')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group mb-3">
									<label for="code" class="form-label">Item Code *</label>
									<input type="text" class="form-control form-control-sm @error('code') is-invalid @enderror"
										id="code" name="code" value="{{ old('code') }}" required>
									@error('code')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-4">
								<div class="form-group mb-3">
									<label for="uom" class="form-label">Unit of Measure</label>
									<select class="form-select form-select-sm" id="uom" name="uom" required>
										<option value="">--Select--</option>
										@foreach($units as $unit)
										<option value="{{ $unit->unit_code }}" {{ old('uom') == $unit->unit_code ? 'selected' : '' }}>
											{{ $unit->unit_name }}
										</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="form-group mb-3">
									<label for="remarks" class="form-label">Remarks</label>
									<input type="text"
										class="form-control form-control-sm @error('remarks') is-invalid @enderror"
										id="remarks" name="remarks" value="{{ old('remarks') }}">
									@error('remarks')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>


						{{--<div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
						<label class="form-check-label" for="is_active">Active Item</label>
				</div>
			</div>--}}

			<div class="form-group">
				<button type="submit" class="btn btn-primary btn-sm">
					<i class="ri-save-line"></i> Create Item
				</button>
				<button type="reset" class="btn btn-outline-secondary btn-sm">
					<i class="ri-refresh-line"></i> Reset Form
				</button>

			</div>
			</form>
		</div>
	</div>
</div>
</div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="alert alert-info">
					<strong>Item:</strong> <span id="modalItemName"></span> (<span id="modalItemCode"></span>)
				</div>

				<form id="addCategoryForm">
					@csrf
					<input type="hidden" id="category_item_id" name="item_id">

					<div class="form-group mb-3">
						<label for="category_name" class="form-label">Category Name *</label>
						<input type="text" class="form-control form-control-sm" id="category_name" name="name" required>
					</div>

					<div class="form-group mb-3">
						<label for="category_description" class="form-label">Description</label>
						<textarea class="form-control form-control-sm" id="category_description" name="description" rows="3"></textarea>
					</div>

					<div class="form-group mb-3">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="category_is_active" name="is_active" value="1" checked>
							<label class="form-check-label" for="category_is_active">Active Category</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="saveCategoryBtn">Save Category</button>
			</div>
		</div>
	</div>
</div>

<!-- View Categories Modal -->
<div class="modal fade" id="viewCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="viewCategoriesModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewCategoriesModalLabel">Categories for <span id="viewItemName"></span> (<span id="viewItemCode"></span>)</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Category Name</th>
								<th>Description</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody id="categoriesTableBody">
							<!-- Categories will be loaded here via JavaScript -->
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
<script>
	// Set item details for category modal
	function setItemForCategory(itemId, itemName, itemCode) {
		$('#category_item_id').val(itemId);
		$('#modalItemName').text(itemName);
		$('#modalItemCode').text(itemCode);

		// Make item info readonly in the form
		$('#modalItemName, #modalItemCode').closest('.alert').addClass('readonly-input');
	}

	// View categories in modal
	function viewCategories(itemId, itemName, itemCode) {
		$('#viewItemName').text(itemName);
		$('#viewItemCode').text(itemCode);

		// Fetch categories for this item
		fetch(`/items/${itemId}/categories`)
			.then(response => response.json())
			.then(data => {
				const tableBody = document.getElementById('categoriesTableBody');
				tableBody.innerHTML = '';

				if (data.categories && data.categories.length > 0) {
					data.categories.forEach(category => {
						const row = document.createElement('tr');
						row.innerHTML = `
                            <td>${category.name}</td>
                            <td>${category.description || '-'}</td>
                            <td>
                                <span class="badge bg-${category.is_active ? 'success' : 'secondary'}">
                                    ${category.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" 
                                        onclick="removeCategoryDirect(${category.id})" title="Remove">
                                    <i class="ri-delete-bin-line align-bottom" style="font-size: 1.2rem;"></i>
                                </button>
                            </td>
                        `;
						tableBody.appendChild(row);
					});
				} else {
					tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No categories found for this item</td></tr>';
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('An error occurred while loading categories.');
			});
	}


	// Save category via AJAX using the correct route
	document.getElementById('saveCategoryBtn').addEventListener('click', function() {
		const itemId = document.getElementById('category_item_id').value;
		const categoryName = document.getElementById('category_name').value;
		const categoryDescription = document.getElementById('category_description').value;
		const isActive = document.getElementById('category_is_active').checked ? 1 : 0;

		if (!categoryName) {
			alert('Please enter a category name');
			return;
		}

		// Create the category data
		const categoryData = {
			name: categoryName,
			description: categoryDescription,
			is_active: isActive,
			is_new: true
		};

		// Get existing categories for this item
		fetch(`/items/${itemId}/categories`)
			.then(response => response.json())
			.then(data => {
				let categories = [];
				if (data.categories && data.categories.length > 0) {
					categories = data.categories.map(cat => ({
						id: cat.id,
						name: cat.name,
						description: cat.description,
						is_active: cat.is_active,
						is_new: false
					}));
				}

				// Add the new category
				categories.push(categoryData);

				// Send update request
				return fetch(`/items/${itemId}/categories`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': '{{ csrf_token() }}',
						'X-Requested-With': 'XMLHttpRequest'
					},
					body: JSON.stringify({
						categories: categories
					})
				});
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Close modal
					$('#addCategoryModal').modal('hide');

					// Show success message
					alert('Category added successfully!');

					// Reset form
					document.getElementById('addCategoryForm').reset();

					// Reload page to see the new category
					location.reload();
				} else {
					alert('Error: ' + data.message);
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('An error occurred while saving the category.');
			});
	});

	// Remove category directly
	function removeCategoryDirect(categoryId) {
		if (confirm('Are you sure you want to remove this category?')) {
			fetch(`/categories/${categoryId}`, {
					method: 'DELETE',
					headers: {
						'X-CSRF-TOKEN': '{{ csrf_token() }}',
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						alert('Category removed successfully!');
						location.reload();
					} else {
						alert('Error removing category: ' + data.message);
					}
				})
				.catch(error => {
					console.error('Error:', error);
					alert('Error removing category');
				});
		}
	}

	// Reset modal when closed
	$('#addCategoryModal').on('hidden.bs.modal', function() {
		document.getElementById('addCategoryForm').reset();
	});
</script>
@endpush