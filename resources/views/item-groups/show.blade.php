@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Item Group Details: {{ $itemGroup->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Group Name:</th>
                                    <td>{{ $itemGroup->name }}</td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $itemGroup->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $itemGroup->createdBy->name }}</td>
                                </tr>
                                <tr>
                                    <th>Created Date:</th>
                                    <td>{{ $itemGroup->created_at->format('d-M-Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $itemGroup->updated_at->format('d-M-Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('item-groups.edit', $itemGroup) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Group
                        </a>
                        <a href="{{ route('item-groups.index') }}" class="btn btn-secondary">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Items in this Group ({{ $itemGroup->items->count() }})</h3>
                </div>
                <div class="card-body">
                    @if($itemGroup->items->count() > 0)
                        <div class="list-group">
                            @foreach($itemGroup->items as $item)
                                <a href="{{ route('items.show', $item) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <small>{{ $item->code }}</small>
                                    </div>
                                    <p class="mb-1">UOM: {{ $item->uom }}</p>
                                    <small class="text-muted">
                                        Status: 
                                        <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </small>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No items found in this group.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection