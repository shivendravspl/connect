@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Item Groups</h5>
                    <a href="{{ route('item-groups.create') }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-box-fill"></i> Add New Group
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>No. of Items</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemGroups as $group)
                                <tr>
                                    <td>{{ $group->name }}</td>
                                    <td>{{ $group->description ?? 'N/A' }}</td>
                                    <td>{{ $group->items->count() }}</td>
                                    <td>{{ $group->createdBy->name }}</td>
                                    <td>
                                        <a href="{{ route('item-groups.show', $group) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="ri-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('item-groups.edit', $group) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="ri-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('item-groups.destroy', $group) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
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
@endsection