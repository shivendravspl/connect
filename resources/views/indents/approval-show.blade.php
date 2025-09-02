@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Approve Indent - {{ $indent->indent_no }}</h5>
                </div>
                <div class="card-body">
                    <!-- Indent Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Indent Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="140px">Indent No:</th>
                                            <td>{{ $indent->indent_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>Department:</th>
                                            <td>{{ $indent->department->department_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Requested By:</th>
                                            <td>{{ $indent->requestedBy->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Indent Date:</th>
                                            <td>{{ $indent->indent_date->format('d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Supply Date:</th>
                                            <td>{{ $indent->estimated_supply_date->format('d-m-Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Purpose</h6>
                                    <p>{{ $indent->purpose }}</p>
                                    @if($indent->quotation_file)
                                    <div class="mt-2">
                                        <strong>Quotation File:</strong>
                                        <a href="{{ Storage::url($indent->quotation_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-download-line"></i> Download
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Approval Form -->
                    <form id="approvalForm" action="{{ route('indents.approve', $indent) }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Items for Approval</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>Requested Qty</th>
                                                <th>Unit</th>
                                                <th>Required Date</th>
                                                <th>Remarks</th>
                                                <th>Approve Qty</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($indent->items as $item)
                                            <tr>
                                                <td>{{ $item->item->name }} ({{ $item->item->code }})</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->item->uom }}</td>
                                                <td>{{ $item->required_date->format('d-m-Y') }}</td>
                                                <td>{{ $item->remarks ?? '-' }}</td>
                                                <td width="120px">
                                                    <input type="number" 
                                                           name="items[{{ $item->id }}][quantity_approve]" 
                                                           value="{{ $item->quantity }}"
                                                           min="0" 
                                                           max="{{ $item->quantity }}"
                                                           step="0.01"
                                                           class="form-control form-control-sm approve-quantity"
                                                           required>
                                                    <input type="hidden" 
                                                           name="items[{{ $item->id }}][id]" 
                                                           value="{{ $item->id }}">
                                                </td>
                                                <td width="100px" class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger reject-item" 
                                                            data-quantity="0">
                                                        <i class="ri-close-line"></i> Reject
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="ri-close-line"></i> Reject Indent
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="ri-check-line"></i> Approve Indent
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Indent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('indents.reject', $indent) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="form-label">Reason for Rejection *</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" 
                                  rows="4" required placeholder="Please specify the reason for rejection"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reject item button functionality
    document.querySelectorAll('.reject-item').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const quantityInput = row.querySelector('.approve-quantity');
            quantityInput.value = '0';
            this.classList.add('btn-danger');
            this.classList.remove('btn-outline-danger');
        });
    });

    // Quantity input change handler
    document.querySelectorAll('.approve-quantity').forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            const rejectButton = row.querySelector('.reject-item');
            if (this.value === '0') {
                rejectButton.classList.add('btn-danger');
                rejectButton.classList.remove('btn-outline-danger');
            } else {
                rejectButton.classList.remove('btn-danger');
                rejectButton.classList.add('btn-outline-danger');
            }
        });
    });

    // Form validation
    document.getElementById('approvalForm').addEventListener('submit', function(e) {
        let hasApprovedItem = false;
        document.querySelectorAll('.approve-quantity').forEach(input => {
            if (parseFloat(input.value) > 0) {
                hasApprovedItem = true;
            }
        });

        if (!hasApprovedItem) {
            e.preventDefault();
            alert('Please approve at least one item or reject the entire indent.');
        }
    });
});
</script>
@endpush