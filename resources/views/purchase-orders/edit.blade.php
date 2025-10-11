@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Edit Purchase Order - {{ $purchaseOrder->po_no }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-sm" id="successAlert">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-sm" id="errorAlert">
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Edit PO Modal (Step 1: Select Indent and View Details) -->
                    <div class="modal fade show" id="editPoModal" tabindex="-1" aria-labelledby="editPoModalLabel" aria-modal="true" role="dialog" style="display: block;">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="editPoModalLabel">Edit Purchase Order - Step 1</h6>
                                    <a href="{{ route('purchase-orders.index') }}" class="btn-close" aria-label="Close"></a>
                                </div>
                                <div class="modal-body">
                                    <form id="poStep1Form">
                                        @csrf
                                        <input type="hidden" name="po_id" value="{{ $purchaseOrder->id }}">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label form-label-sm">Indent</label>
                                                <select name="indent_id" id="indent-select" class="form-control form-control-sm select2" required>
                                                    <option value="">Select Indent</option>
                                                    @foreach($indents as $indent)
                                                        <option value="{{ $indent->id }}" {{ $purchaseOrder->indent_id == $indent->id ? 'selected' : '' }}>
                                                            {{ $indent->indent_no }} ({{ $indent->indent_date->format('d M, Y') }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span id="indent-error" class="text-danger small d-none"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label form-label-sm">Vendor</label>
                                                <select name="vendor_id" id="vendor_id" class="form-control form-control-sm select2" required>
                                                    <option value="">Select Vendor</option>
                                                    @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}" {{ $purchaseOrder->vendor_id == $vendor->id ? 'selected' : '' }}>
                                                            {{ $vendor->company_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span id="vendor-error" class="text-danger small d-none"></span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label form-label-sm">PO Date</label>
                                                <input type="date" name="po_date" id="po_date" class="form-control form-control-sm" value="{{ $purchaseOrder->po_date->format('Y-m-d') }}" required>
                                                <span id="po_date-error" class="text-danger small d-none"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label form-label-sm">Expected Delivery Date</label>
                                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control form-control-sm" value="{{ $purchaseOrder->expected_delivery_date->format('Y-m-d') }}" required>
                                                <span id="expected_delivery_date-error" class="text-danger small d-none"></span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label class="form-label form-label-sm">Terms</label>
                                                <textarea name="terms" id="terms" rows="1" class="form-control form-control-sm">{{ $purchaseOrder->terms }}</textarea>
                                                <span id="terms-error" class="text-danger small d-none"></span>
                                            </div>
                                        </div>

                                        <h5 class="h6">Indent Items (Read-Only)</h5>
                                        <div class="table-responsive">
                                            <table class="table table-centered table-striped table-sm" id="items-table">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Approved Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Subtotal</th>
                                                        <th>Required Date</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="items-body">
                                                    @if($indentItems->isEmpty())
                                                        <tr><td colspan="6">No items found.</td></tr>
                                                    @else
                                                        @foreach($indentItems as $item)
                                                            <tr>
                                                                <td class="small">{{ $item->item ? $item->item->name : 'N/A' }}</td>
                                                                <td class="small">{{ $purchaseOrder->status === 'draft' ? $item->quantity_approve : $item->quantity }}</td>
                                                                <td class="small">{{ $purchaseOrder->status === 'issued' ? $item->unit_price : '-' }}</td>
                                                                <td class="small">{{ $purchaseOrder->status === 'issued' ? $item->subtotal : '-' }}</td>
                                                                <td class="small">{{ ($purchaseOrder->status === 'draft' ? $item->required_date : $item->required_date)->format('d M, Y') }}</td>
                                                                <td class="small">{{ $purchaseOrder->status === 'issued' ? $item->remarks : '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                                    <button type="button" class="btn btn-primary btn-sm" id="nextBtn" {{ $indentItems->isEmpty() ? 'disabled' : '' }}>Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Items Modal (Step 2) -->
                    <div class="modal fade" id="editItemsModal" tabindex="-1" aria-labelledby="editItemsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="editItemsModalLabel">Edit Purchase Order - Step 2</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="poStep2Form" method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="indent_id" id="step2_indent_id" value="{{ $purchaseOrder->indent_id }}">
                                        <input type="hidden" name="vendor_id" id="step2_vendor_id" value="{{ $purchaseOrder->vendor_id }}">
                                        <input type="hidden" name="po_date" id="step2_po_date" value="{{ $purchaseOrder->po_date->format('Y-m-d') }}">
                                        <input type="hidden" name="expected_delivery_date" id="step2_expected_delivery_date" value="{{ $purchaseOrder->expected_delivery_date->format('Y-m-d') }}">
                                        <input type="hidden" name="terms" id="step2_terms" value="{{ $purchaseOrder->terms }}">

                                        <h5 class="h6">Edit Items</h5>
                                        <div class="table-responsive">
                                            <table class="table table-centered table-striped table-sm" id="edit-items-table">
                                                <thead>
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Approved Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Subtotal</th>
                                                        <th>Required Date</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit-items-body">
                                                    <!-- Populated dynamically -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editPoModal">Back</button>
                                    <button type="submit" class="btn btn-primary btn-sm" form="poStep2Form">Update PO</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('User authenticated:', {{ Auth::check() ? 'true' : 'false' }});

    const editPoModal = document.getElementById('editPoModal');
    editPoModal.addEventListener('shown.bs.modal', function() {
        $('#indent-select').select2({
            placeholder: 'Select Indent',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editPoModal')
        });

        $('#vendor_id').select2({
            placeholder: 'Select Vendor',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editPoModal')
        });

        console.log('Indent select options:', $('#indent-select').find('option').length);
        console.log('Vendor select options:', $('#vendor_id').find('option').length);
    });

    editPoModal.addEventListener('hidden.bs.modal', function() {
        $('#indent-select').select2('destroy');
        $('#vendor_id').select2('destroy');
        window.location.href = '{{ route('purchase-orders.index') }}';
    });

    const editItemsModal = document.getElementById('editItemsModal');
    editItemsModal.addEventListener('hidden.bs.modal', function() {
        window.location.reload();
    });

    function attachSubtotalListeners() {
        document.querySelectorAll('.unit-price').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const quantity = parseFloat(row.querySelector('.quantity').value);
                const unitPrice = parseFloat(this.value);
                const subtotalInput = row.querySelector('.subtotal');
                if (quantity && unitPrice) {
                    subtotalInput.value = (quantity * unitPrice).toFixed(2);
                } else {
                    subtotalInput.value = '';
                }
            });
        });
    }

    let selectedItems = [];
    $('#indent-select').on('change', function() {
        const indentId = this.value;
        const itemsBody = document.getElementById('items-body');
        const nextBtn = document.getElementById('nextBtn');
        itemsBody.innerHTML = '<tr><td colspan="6">Loading items...</td></tr>';

        if (indentId) {
            fetch('{{ route('purchase-orders.get-indent-items') }}?indent_id=' + indentId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error('Network response was not ok: ' + response.status + ' - ' + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.error) {
                    itemsBody.innerHTML = '<tr><td colspan="6" class="text-danger">' + data.error + '</td></tr>';
                    nextBtn.disabled = true;
                    return;
                }

                selectedItems = data.items || [];
                itemsBody.innerHTML = selectedItems.length > 0 ? selectedItems.map(item => `
                    <tr>
                        <td class="small">${item.item_name}</td>
                        <td class="small">${item.quantity_approve}</td>
                        <td class="small">-</td>
                        <td class="small">-</td>
                        <td class="small">${new Date(item.required_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                        <td class="small">-</td>
                    </tr>
                `).join('') : '<tr><td colspan="6">No items found.</td></tr>';
                nextBtn.disabled = selectedItems.length === 0;
            })
            .catch(error => {
                itemsBody.innerHTML = '<tr><td colspan="6" class="text-danger">Error loading items: ' + error.message + '</td></tr>';
                nextBtn.disabled = true;
                console.error('AJAX error:', error);
            });
        } else {
            itemsBody.innerHTML = '<tr><td colspan="6">Please select an indent.</td></tr>';
            nextBtn.disabled = true;
        }
    });

    $('#nextBtn').on('click', function() {
        const form = document.getElementById('poStep1Form');
        const indentId = form.querySelector('#indent-select').value;
        const vendorId = form.querySelector('#vendor_id').value;
        const poDate = form.querySelector('#po_date').value;
        const expectedDeliveryDate = form.querySelector('#expected_delivery_date').value;
        const terms = form.querySelector('#terms').value;
        const poId = form.querySelector('[name="po_id"]').value;

        let isValid = true;
        if (!indentId) {
            document.getElementById('indent-error').textContent = 'Please select an indent.';
            document.getElementById('indent-error').classList.remove('d-none');
            isValid = false;
        } else {
            document.getElementById('indent-error').classList.add('d-none');
        }
        if (!vendorId) {
            document.getElementById('vendor-error').textContent = 'Please select a vendor.';
            document.getElementById('vendor-error').classList.remove('d-none');
            isValid = false;
        } else {
            document.getElementById('vendor-error').classList.add('d-none');
        }
        if (!poDate) {
            document.getElementById('po_date-error').textContent = 'Please select a PO date.';
            document.getElementById('po_date-error').classList.remove('d-none');
            isValid = false;
        } else {
            document.getElementById('po_date-error').classList.add('d-none');
        }
        if (!expectedDeliveryDate) {
            document.getElementById('expected_delivery_date-error').textContent = 'Please select an expected delivery date.';
            document.getElementById('expected_delivery_date-error').classList.remove('d-none');
            isValid = false;
        } else if (new Date(expectedDeliveryDate) < new Date(poDate)) {
            document.getElementById('expected_delivery_date-error').textContent = 'Expected delivery date must be on or after PO date.';
            document.getElementById('expected_delivery_date-error').classList.remove('d-none');
            isValid = false;
        } else {
            document.getElementById('expected_delivery_date-error').classList.add('d-none');
        }

        if (isValid && selectedItems.length > 0) {
            const updateUrl = '{{ route("purchase-orders.update", ":poId") }}'.replace(':poId', poId);
            console.log('Setting poStep2Form action:', updateUrl);
            document.getElementById('poStep2Form').action = updateUrl;
            document.getElementById('step2_indent_id').value = indentId;
            document.getElementById('step2_vendor_id').value = vendorId;
            document.getElementById('step2_po_date').value = poDate;
            document.getElementById('step2_expected_delivery_date').value = expectedDeliveryDate;
            document.getElementById('step2_terms').value = terms;

            const editItemsBody = document.getElementById('edit-items-body');
            editItemsBody.innerHTML = selectedItems.map((item, index) => `
                <tr>
                    <td class="small">${item.item_name}</td>
                    <td>
                        <input type="hidden" name="items[${index}][indent_item_id]" value="${item.id}">
                        <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                        <input type="number" step="0.01" name="items[${index}][quantity]" value="${item.quantity_approve}" class="form-control form-control-sm quantity" readonly required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${index}][unit_price]" value="0.01" class="form-control form-control-sm unit-price" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${index}][subtotal]" class="form-control form-control-sm subtotal" readonly>
                    </td>
                    <td>
                        <input type="date" name="items[${index}][required_date]" value="${item.required_date}" class="form-control form-control-sm" required>
                    </td>
                    <td>
                        <input type="text" name="items[${index}][remarks]" class="form-control form-control-sm">
                    </td>
                </tr>
            `).join('');

            attachSubtotalListeners();

            const editPoModal = bootstrap.Modal.getInstance(document.getElementById('editPoModal'));
            editPoModal.hide();
            const editItemsModal = new bootstrap.Modal(document.getElementById('editItemsModal'));
            editItemsModal.show();

            const step2Form = document.getElementById('poStep2Form');
            step2Form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(step2Form);
                console.log('Submitting poStep2Form to:', step2Form.action);
                console.log('Form data:', Object.fromEntries(formData));
                fetch(step2Form.action, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Update response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Update request failed: ' + response.status + ' - ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Update response data:', data);
                    window.location.href = '{{ route('purchase-orders.show', ':poId') }}'.replace(':poId', poId);
                })
                .catch(error => {
                    console.error('Update error:', error);
                    alert('Failed to update PO: ' + error.message);
                });
            });
        }
    });
});
</script>
@endpush
@endsection