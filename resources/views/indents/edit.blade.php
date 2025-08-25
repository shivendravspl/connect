@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Indent - {{ $indent->indent_no }}</h5>
                </div>
                <div class="card-body">
                    <!-- Header Form -->
                    <form id="headerForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="indent_id" name="indent_id" value="{{ $indent->id }}">

                        <!-- Indent Header Information -->
                        <div id="indentHeaderSection">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label for="department_id" class="small fw-bold">Select Department *</label>
                                        <select class="form-control form-control-sm" id="department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $indent->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->department_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label for="indent_date" class="small fw-bold">Indent Date *</label>
                                        <input type="date" class="form-control form-control-sm" id="indent_date"
                                            name="indent_date" value="{{ old('indent_date', $indent->indent_date->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label for="estimated_supply_date" class="small fw-bold">Estimated Supply Date *</label>
                                        <input type="date" class="form-control form-control-sm" id="estimated_supply_date"
                                            name="estimated_supply_date" value="{{ old('estimated_supply_date', $indent->estimated_supply_date->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="order_by" class="small fw-bold">Order By *</label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $staffMembers->name }}" disabled>
                                        <input type="hidden" id="order_by" name="order_by" value="{{ $staffMembers->id }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="quotation_file" class="small fw-bold">Quotation File</label>
                                        <input type="file" class="form-control-file form-control-sm" id="quotation_file" name="quotation_file">
                                        @if($indent->quotation_file)
                                        <small class="form-text text-muted">
                                            Current file: <a href="{{ Storage::url($indent->quotation_file) }}" target="_blank">View</a>
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-2">
                                        <label for="purpose" class="small fw-bold">Purpose of Indent *</label>
                                        <textarea class="form-control form-control-sm" id="purpose" name="purpose"
                                            rows="2">{{ old('purpose', $indent->purpose) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="button" id="saveHeader" class="btn btn-primary btn-sm">
                                    <i class="ri-save-line"></i> Save Indent Header
                                </button>
                                <a href="{{ route('indents.index') }}" class="btn btn-light btn-sm">
                                    <i class="ri-close-line"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Items Section (initially hidden) -->
                    <div id="itemsSection" style="display: none;">
                        <!-- Saved Indent Details Display -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <h6 class="mb-2">Indent Details</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <small><strong>Department:</strong> <span id="display_department"></span></small>
                                            </div>
                                            <div class="col-md-3">
                                                <small><strong>Indent Date:</strong> <span id="display_indent_date"></span></small>
                                            </div>
                                            <div class="col-md-3">
                                                <small><strong>Supply Date:</strong> <span id="display_supply_date"></span></small>
                                            </div>
                                            <div class="col-md-3">
                                                <small><strong>Order By:</strong> <span id="display_order_by"></span></small>
                                            </div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="col-md-12">
                                                <small><strong>Purpose:</strong> <span id="display_purpose"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Form -->


                        <div class="row">
                            <!-- Left Column: Items Input Form -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Items Required</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="itemsContainer">
                                            <!-- Items will be added dynamically here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('indents.update', $indent) }}" method="POST" id="itemsForm" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="indent_id" value="{{ $indent->id }}">
                                <!-- Right Column: Items Listing -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Items Added</h6>
                                            <span class="badge bg-primary" id="itemsCount">0 Items</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-2" id="itemsTable">
                                                    <thead class="small">
                                                        <tr>
                                                            <th>Item</th>
                                                            <th>Qty</th>
                                                            <th>Unit</th>
                                                            <th>Req Date</th>
                                                            <th>Spec</th>
                                                            <th width="80px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Items will be listed here dynamically -->
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Submit Button at bottom of items list -->
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <button type="button" id="backToHeader" class="btn btn-secondary btn-sm">
                                                    <i class="ri-arrow-left-line"></i> Back to Header
                                                </button>
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="ri-check-line"></i> Update Indent with Items
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template for item row -->
<template id="itemTemplate">
    <div class="item-row mb-2 p-2 border rounded">
        <div class="row g-2">
            <div class="col-md-5">
                <div class="form-group mb-1">
                    <label class="small fw-bold">Item *</label>
                    <select class="form-control form-control-sm item-select" name="items[][item_id]">
                        <option value="">Select Item</option>
                        @foreach($itemGroups as $group)
                        <optgroup label="{{ $group->name }}">
                            @foreach($group->items as $item)
                            <option value="{{ $item->id }}"
                                data-uom="{{ $item->uom }}">
                                {{ $item->name }} ({{ $item->code }})
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-1">
                    <label class="small fw-bold">Qty *</label>
                    <input type="number" step="0.01" class="form-control form-control-sm quantity"
                        name="items[][quantity]">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-1">
                    <label class="small fw-bold">Unit</label>
                    <input type="text" class="form-control form-control-sm uom" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-1">
                    <label class="small fw-bold">Req Date *</label>
                    <input type="date" class="form-control form-control-sm required-date" name="items[][required_date]" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group mb-1">
                    <label class="small fw-bold">Specification</label>
                    <input type="text" class="form-control form-control-sm specification" name="items[][specification]">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-1 d-flex align-items-end">
                    <button type="button" class="btn btn-info btn-sm save-item ms-auto">
                        <i class="ri-save-line"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('itemsContainer');
        const template = document.getElementById('itemTemplate');
        const saveHeaderBtn = document.getElementById('saveHeader');
        const backToHeaderBtn = document.getElementById('backToHeader');
        const headerSection = document.getElementById('indentHeaderSection');
        const itemsSection = document.getElementById('itemsSection');
        const itemsTable = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
        const itemsCount = document.getElementById('itemsCount');

        let itemCount = 0;
        let savedItems = [];

        // Set default required date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];

        // Pre-populate savedItems with existing items
        @foreach($indent-> items as $item)
        savedItems.push({
            item_id: '{{ $item->item_id }}',
            item_text: '{{ $item->item->name }} ({{ $item->item->code }})',
            quantity: '{{ $item->quantity }}',
            uom: '{{ $item->item->uom }}',
            required_date: '{{ $item->required_date->format("Y-m-d") }}',
            specification: '{{ $item->specification ?? "" }}',
            row_index: itemCount
        });
        itemCount++;
        @endforeach

        // Save header and show items section
        saveHeaderBtn.addEventListener('click', function() {
            // Basic validation
            const department = document.getElementById('department_id');
            const indentDate = document.getElementById('indent_date');
            const supplyDate = document.getElementById('estimated_supply_date');
            const orderBy = document.getElementById('order_by');
            const purpose = document.getElementById('purpose');

            if (!department.value || !indentDate.value || !supplyDate.value || !orderBy.value || !purpose.value) {
                alert('Please fill all required fields before proceeding.');
                return;
            }

            // Show loading state
            saveHeaderBtn.disabled = true;
            saveHeaderBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Saving...';

            // Prepare form data
            const formData = new FormData(document.getElementById('headerForm'));

            // Send AJAX request to save header
            fetch('{{ route("indents.saveHeader", $indent) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error('Received non-JSON response: ' + text.substring(0, 100));
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update display values
                        document.getElementById('display_department').textContent = department.options[department.selectedIndex].text;
                        document.getElementById('display_indent_date').textContent = indentDate.value;
                        document.getElementById('display_supply_date').textContent = supplyDate.value;
                        document.getElementById('display_order_by').textContent = document.querySelector('input[disabled][value]').value;
                        document.getElementById('display_purpose').textContent = purpose.value;

                        // Hide header, show items section
                        headerSection.style.display = 'none';
                        itemsSection.style.display = 'block';

                        // Refresh items table with existing items
                        refreshItemsTable();

                        // Add new item form
                        if (container.children.length === 0) {
                            addItem();
                        }
                    } else {
                        alert('Error saving indent header: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    alert('Error saving indent header: ' + error.message);
                })
                .finally(() => {
                    // Reset button state
                    saveHeaderBtn.disabled = false;
                    saveHeaderBtn.innerHTML = '<i class="ri-save-line"></i> Save Indent Header';
                });
        });

        // Back to header section
        backToHeaderBtn.addEventListener('click', function() {
            itemsSection.style.display = 'none';
            headerSection.style.display = 'block';
            container.innerHTML = ''; // Clear item form
        });

        function addItem() {
            // Clear container first to ensure only one row exists
            container.innerHTML = '';

            const clone = template.content.cloneNode(true);
            const itemRow = clone.querySelector('.item-row');

            // Update names with index
            const selects = itemRow.querySelectorAll('[name]');
            selects.forEach(el => {
                el.name = el.name.replace('[]', `[${itemCount}]`);
            });

            // Set default required date
            const requiredDateInput = itemRow.querySelector('.required-date');
            requiredDateInput.value = tomorrowStr;

            // Add UOM change handler
            const itemSelect = itemRow.querySelector('.item-select');
            const uomField = itemRow.querySelector('.uom');

            itemSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                uomField.value = selectedOption.dataset.uom || '';
            });

            // Add save item button handler
            itemRow.querySelector('.save-item').addEventListener('click', function() {
                saveItem(itemRow);
            });

            container.appendChild(itemRow);
            itemCount++;
        }

        function saveItem(itemRow) {
            const itemSelect = itemRow.querySelector('.item-select');
            const quantityInput = itemRow.querySelector('.quantity');
            const uomInput = itemRow.querySelector('.uom');
            const requiredDateInput = itemRow.querySelector('.required-date');
            const specificationInput = itemRow.querySelector('.specification');

            // Strict validation
            if (!itemSelect.value) {
                alert('Please select an item.');
                itemSelect.focus();
                return;
            }
            if (!quantityInput.value || parseFloat(quantityInput.value) <= 0) {
                alert('Please enter a valid quantity greater than 0.');
                quantityInput.focus();
                return;
            }
            if (!requiredDateInput.value) {
                alert('Please select a required date.');
                requiredDateInput.focus();
                return;
            }

            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            const itemText = selectedOption.text;

            // Add to saved items array
            const itemData = {
                item_id: itemSelect.value,
                item_text: itemText,
                quantity: parseFloat(quantityInput.value).toFixed(2),
                uom: uomInput.value,
                required_date: requiredDateInput.value,
                specification: specificationInput.value || '',
                row_index: itemCount
            };

            savedItems.push(itemData);

            // Add to items table
            addItemToTable(itemData);

            // Clear the form row for next entry
            itemSelect.value = '';
            quantityInput.value = '';
            uomInput.value = '';
            requiredDateInput.value = tomorrowStr;
            specificationInput.value = '';

            // Update items count
            updateItemsCount();

            // Show success message
            alert('Item saved successfully! You can add another item.');
        }

        function addItemToTable(itemData) {
            const newRow = itemsTable.insertRow();
            newRow.className = 'small';

            newRow.innerHTML = `
                <td>${itemData.item_text}</td>
                <td>${itemData.quantity}</td>
                <td>${itemData.uom}</td>
                <td>${itemData.required_date}</td>
                <td>${itemData.specification || '-'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-warning edit-item" data-index="${itemData.row_index}" title="Edit">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-item" data-index="${itemData.row_index}" title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            `;

            // Add event listeners for edit and delete
            newRow.querySelector('.edit-item').addEventListener('click', function() {
                editItem(itemData.row_index);
            });

            newRow.querySelector('.delete-item').addEventListener('click', function() {
                deleteItem(itemData.row_index);
            });
        }

        function editItem(index) {
            // Find the item in savedItems array
            const itemIndex = savedItems.findIndex(item => item.row_index === index);
            if (itemIndex === -1) return;

            const item = savedItems[itemIndex];

            // Ensure item form exists
            if (container.children.length === 0) {
                addItem();
            }

            const itemRow = container.querySelector('.item-row');
            if (!itemRow) return;

            const itemSelect = itemRow.querySelector('.item-select');
            const quantityInput = itemRow.querySelector('.quantity');
            const uomInput = itemRow.querySelector('.uom');
            const requiredDateInput = itemRow.querySelector('.required-date');
            const specificationInput = itemRow.querySelector('.specification');

            itemSelect.value = item.item_id;
            quantityInput.value = item.quantity;
            uomInput.value = item.uom;
            requiredDateInput.value = item.required_date;
            specificationInput.value = item.specification || '';

            // Remove the item from saved items and table
            savedItems.splice(itemIndex, 1);
            refreshItemsTable();
        }

        function deleteItem(index) {
            if (!confirm('Are you sure you want to delete this item?')) return;

            // Find the item in savedItems array
            const itemIndex = savedItems.findIndex(item => item.row_index === index);
            if (itemIndex === -1) return;

            // Remove the item
            savedItems.splice(itemIndex, 1);
            refreshItemsTable();
        }

        function refreshItemsTable() {
            // Clear the table
            itemsTable.innerHTML = '';

            // Re-add all items
            savedItems.forEach(item => {
                addItemToTable(item);
            });

            // Update items count
            updateItemsCount();
        }

        function updateItemsCount() {
            itemsCount.textContent = savedItems.length + ' Item' + (savedItems.length !== 1 ? 's' : '');
        }

        // Before items form submission, add hidden inputs for saved items
        document.getElementById('itemsForm').addEventListener('submit', function(e) {
            if (savedItems.length === 0) {
                e.preventDefault();
                alert('Please add at least one item before submitting.');
                return;
            }

            // Remove any existing hidden inputs first
            const existingHiddenInputs = this.querySelectorAll('input[type="hidden"][name^="items"]');
            existingHiddenInputs.forEach(input => input.remove());

            // Add hidden inputs for each saved item
            savedItems.forEach((item, index) => {
                if (!item.item_id || !item.quantity || !item.required_date) {
                    return; // Skip invalid items
                }
                const itemIdInput = document.createElement('input');
                itemIdInput.type = 'hidden';
                itemIdInput.name = `items[${index}][item_id]`;
                itemIdInput.value = item.item_id;
                this.appendChild(itemIdInput);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = `items[${index}][quantity]`;
                quantityInput.value = item.quantity;
                this.appendChild(quantityInput);

                const requiredDateInput = document.createElement('input');
                requiredDateInput.type = 'hidden';
                requiredDateInput.name = `items[${index}][required_date]`;
                requiredDateInput.value = item.required_date;
                this.appendChild(requiredDateInput);

                const specificationInput = document.createElement('input');
                specificationInput.type = 'hidden';
                specificationInput.name = `items[${index}][specification]`;
                specificationInput.value = item.specification || '';
                this.appendChild(specificationInput);
            });
        });
    });
</script>
@endpush