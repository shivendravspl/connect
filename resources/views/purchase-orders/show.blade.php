@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Purchase Order Details: {{ $purchaseOrder->po_no }}</h4>
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

                    <h5 class="h6">PO Information</h5>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <p class="small"><strong>PO No:</strong> {{ $purchaseOrder->po_no }}</p>
                            <p class="small"><strong>Indent No:</strong> {{ $purchaseOrder->indent->indent_no }}</p>
                            <p class="small"><strong>Vendor:</strong> {{ $purchaseOrder->vendor->company_name }}</p>
                            <p class="small"><strong>PO Date:</strong> {{ $purchaseOrder->po_date->format('d M, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="small"><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date->format('d M, Y') }}</p>
                            <p class="small"><strong>Total Amount:</strong> {{ number_format($purchaseOrder->total_amount, 2) }}</p>
                            <p class="small"><strong>Status:</strong>
                                @if($purchaseOrder->status == 'issued')
                                    <span class="badge bg-primary">Issued</span>
                                @elseif($purchaseOrder->status == 'sent')
                                    <span class="badge bg-info">Sent</span>
                                @elseif($purchaseOrder->status == 'acknowledged')
                                    <span class="badge bg-warning">Acknowledged</span>
                                @elseif($purchaseOrder->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($purchaseOrder->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </p>
                            <p class="small"><strong>Created By:</strong> {{ $purchaseOrder->createdBy->name }}</p>
                        </div>
                    </div>

                    <h5 class="h6 mt-3">Items</h5>
                    <div class="table-responsive">
                        <table class="table table-centered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Required Date</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="small">{{ $item->item->name }}</td>
                                    <td class="small">{{ $item->quantity }}</td>
                                    <td class="small">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="small">{{ number_format($item->subtotal, 2) }}</td>
                                    <td class="small">{{ $item->required_date->format('d M, Y') }}</td>
                                    <td class="small">{{ $item->remarks ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2">
                        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure you want to delete this PO?')) { document.getElementById('delete-po').submit(); }">Delete</button>
                        <form id="delete-po" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(successAlert);
            bsAlert.close();
        }, 2000);
    }

    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(errorAlert);
            bsAlert.close();
        }, 4000);
    }
});
</script>
@endpush