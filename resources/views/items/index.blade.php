@extends('layouts.app')

@section('content')
<div class="container-fluid">
	<div class="row">
        <div class="col-12">
            <div
                class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Item Master</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">Item Master</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5>Item Master</h5>
					<div class="d-flex">
						<!-- Export buttons container -->
						<div class="btn-group me-2">
							<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="ri-download-line"></i> Export
							</button>
							<ul class="dropdown-menu">
								<li><a class="dropdown-item" href="{{ route('items.export') }}">Export Items</a></li>
								<li><a class="dropdown-item" href="{{ route('categories.export') }}">Export Categories</a></li>
							</ul>
						</div>
						<!-- Add item button -->
						<a href="{{ route('items.create') }}" class="btn btn-success btn-sm">
							<i class="ri-add-box-fill"></i> Add Item
						</a>
					</div>
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
									<th>Code</th>
									<th>Name</th>
									<th>Group</th>
									<th>UOM</th>
									<th>Status</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								@foreach($items as $item)
								<tr>
									<td>{{ $item->code }}</td>
									<td>{{ $item->name }}</td>
									<td>{{ $item->itemGroup->name }}</td>
									<td>{{ $item->uom }}</td>
									<td>
										<span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">
											{{ $item->is_active ? 'Active' : 'Inactive' }}
										</span>
									</td>

									<td>
										<a href="{{ route('items.show', $item) }}"
											class="btn btn-sm btn-info">
											<i class="ri-eye-fill"></i>
										</a>
										<a href="{{ route('items.edit', $item) }}"
											class="btn btn-sm btn-primary">
											<i class="ri-pencil-fill"></i>
										</a>
										<form action="{{ route('items.destroy', $item) }}"
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

					<div class="d-flex justify-content-center">
						{{ $items->links() }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection