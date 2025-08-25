@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Item Details: {{ $item->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Item Code:</th>
                                    <td>{{ $item->code }}</td>
                                </tr>
                                <tr>
                                    <th>Item Name:</th>
                                    <td>{{ $item->name }}</td>
                                </tr>
                                <tr>
                                    <th>Item Group:</th>
                                    <td>{{ $item->itemGroup->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $item->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Unit of Measure:</th>
                                    <td>{{ $item->uom }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-primary">
                            <i class="ri-pencil-fill"></i>
                        </a>
                        <a href="{{ route('items.index') }}" class="btn btn-sm btn-secondary">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Indent History</h5>
                </div>
                <div class="card-body">
                    @if($item->indentItems->count() > 0)
                        <div class="list-group">
                            @foreach($item->indentItems->take(5) as $indentItem)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Indent #{{ $indentItem->indent->indent_no }}</h6>
                                        <small>{{ $indentItem->created_at->format('d-M-Y') }}</small>
                                    </div>
                                    <p class="mb-1">Quantity: {{ $indentItem->quantity }} {{ $item->uom }}</p>
                                    <small class="text-muted">
                                        Status: 
                                        <span class="badge badge-{{ $indentItem->indent->status_class }}">
                                            {{ ucfirst($indentItem->indent->status) }}
                                        </span>
                                    </small>
                                </div>
                            @endforeach
                        </div>
                        @if($item->indentItems->count() > 5)
                            <div class="text-center mt-2">
                                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No indent history found for this item.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection