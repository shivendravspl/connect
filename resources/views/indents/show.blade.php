@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Indent Details - {{ $indent->indent_no }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Indent Information</h4>
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Indent Number:</strong><br> {{ $indent->indent_no }}</p>
                            <p><strong>Department:</strong><br> {{ $indent->department->department_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Indent Date:</strong><br> {{ $indent->indent_date->format('d M, Y') }}</p>
                            <p><strong>Estimated Supply Date:</strong><br> {{ $indent->estimated_supply_date->format('d M, Y') }}</p>
                            <p><strong>Order By:</strong><br> {{ $indent->orderByUser->name }}</p>
                        </div>
                    </div>
                    
                    <p><strong>Purpose:</strong><br> {{ $indent->purpose }}</p>
                    
                    @if($indent->quotation_file)
                    <p>
                        <strong>Quotation File:</strong><br>
                        <a href="{{ Storage::url($indent->quotation_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            View Quotation
                        </a>
                    </p>
                    @endif
                    
                    <p><strong>Status:</strong> 
                        @if($indent->status == 'draft')
                            <span class="badge bg-secondary">Draft</span>
                        @elseif($indent->status == 'submitted')
                            <span class="badge bg-primary">Submitted</span>
                        @elseif($indent->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($indent->status == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </p>
                    
                    @if($indent->status == 'approved')
                        <p><strong>Approved By:</strong> {{ $indent->approvedBy->name }}</p>
                        <p><strong>Approved At:</strong> {{ $indent->approved_at->format('d M, Y H:i') }}</p>
                    @endif
                    
                    @if($indent->status == 'rejected')
                        <p><strong>Rejection Reason:</strong> {{ $indent->rejection_reason }}</p>
                    @endif
                    
                    <div class="mt-3">
                        @if($indent->status == 'draft')
                            <form action="{{ route('indents.submit', $indent) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">Submit for Approval</button>
                            </form>
                            <a href="{{ route('indents.edit', $indent) }}" class="btn btn-sm btn-secondary"><i class="ri-pencil-fill align-bottom"></i></a>
                        @endif
                        
                        @if(Auth::user()->can_approve_indents && $indent->status == 'submitted')
                            <form action="{{ route('indents.approve', $indent) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Approve</button>
                            </form>
                            
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                Reject
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Items</h4>
                    
                    <div class="table-responsive">
                        <table class="table table-centered table-striped">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Required Date</th>
                                    <th>Specification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($indent->items as $item)
                                <tr>
                                    <td>{{ $item->item->name }} ({{ $item->item->code }})</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->item->uom }}</td>
                                    <td>{{ $item->required_date->format('d M, Y') }}</td>
                                    <td>{{ $item->remarks ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->can_approve_indents && $indent->status == 'submitted')
<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reject Indent</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <form action="{{ route('indents.reject', $indent) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Reject Indent</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection