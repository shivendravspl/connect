@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Indent Management</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <a href="{{ route('indents.create') }}" class="btn btn-danger mb-2">
                                <i class="ri-add-circle-line align-middle"></i> Create Indent
                            </a>
                        </div>
                    </div>

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

                    <div class="table-responsive">
                        <table class="table table-centered table-striped">
                            <thead>
                                <tr>
                                    <th>Indent No</th>
                                    <th>Department</th>
                                    <th>Requested By</th>
                                    <th>Indent Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($indents as $indent)
                                <tr>
                                    <td>{{ $indent->indent_no }}</td>
                                    <td>{{ $indent->department_id }}</td>
                                    <td>{{ $indent->requestedBy->name }}</td>
                                    <td>{{ $indent->indent_date->format('d M, Y') }}</td>
                                    <td>
                                        @if($indent->status == 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($indent->status == 'submitted')
                                            <span class="badge bg-primary">Submitted</span>
                                        @elseif($indent->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($indent->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('indents.show', $indent) }}" class="action-icon"> <i class="ri-eye-line"></i></a>
                                        @if($indent->status == 'draft')
                                            <a href="{{ route('indents.edit', $indent) }}" class="action-icon"> <i class="ri-edit-line"></i></a>
                                            <form action="{{ route('indents.destroy', $indent) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn action-icon" onclick="return confirm('Are you sure you want to delete this indent?')">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $indents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection