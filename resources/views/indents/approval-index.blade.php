@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Indents Pending Approval</h5>
                </div>
                <div class="card-body">
                    @if($indents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Indent No</th>
                                        <th>Department</th>
                                        <th>Requested By</th>
                                        <th>Indent Date</th>
                                        <th>Items Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($indents as $indent)
                                    <tr>
                                        <td>{{ $indent->indent_no }}</td>
                                        <td>{{ $indent->department->department_name }}</td>
                                        <td>{{ $indent->requestedBy->name }}</td>
                                        <td>{{ $indent->indent_date->format('d-m-Y') }}</td>
                                        <td>{{ $indent->items->count() }}</td>
                                        <td>
                                            <a href="{{ route('indents.approval.show', $indent) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="ri-eye-line"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $indents->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="ri-information-line"></i> No indents pending approval.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection