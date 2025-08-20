@extends('layouts.app')

@section('content')
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<h5>Edit Item: {{ $item->name }}</h5>
				</div>
				<div class="card-body">
					<form action="{{ route('items.update', $item) }}" method="POST">
						@csrf
						@method('PUT')

						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="item_group_id">Item Group *</label>
									<select class="form-control form-control-sm @error('item_group_id') is-invalid @enderror"
										id="item_group_id" name="item_group_id" required>
										<option value="">Select Group</option>
										@foreach($itemGroups as $group)
										<option value="{{ $group->id }}" {{ old('item_group_id', $item->item_group_id) == $group->id ? 'selected' : '' }}>
											{{ $group->name }}
										</option>
										@endforeach
									</select>
									@error('item_group_id')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="name">Item Name *</label>
									<input type="text" class="form-control  form-control-sm @error('name') is-invalid @enderror"
										id="name" name="name" value="{{ old('name', $item->name) }}" required>
									@error('name')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="code">Item Code *</label>
									<input type="text" class="form-control form-control-sm @error('code') is-invalid @enderror"
										id="code" name="code" value="{{ old('code', $item->code) }}" required>
									@error('code')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
						</div>

						<div class="row">

							<div class="col-md-4">
								<div class="form-group mb-3">
									<label for="uom">Unit of Measure *</label>
									<select class="form-control form-control-sm @error('uom') is-invalid @enderror"
										id="uom" name="uom" required>
										<option value="">--Select--</option>
										@foreach($units as $unit)
										<option value="{{ $unit->unit_code }}"
											{{ old('uom', $item->uom) == $unit->unit_code ? 'selected' : '' }}>
											{{ $unit->unit_name }}
										</option>
										@endforeach
									</select>
									@error('uom')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>

							<div class="col-md-4">
								<div class="form-group">
									<label for="description">Description</label>
									<input type="text" class="form-control  form-control-sm @error('description') is-invalid @enderror"
										id="description" name="description" value="{{ old('description', $item->description) }}" required>
									@error('description')
									<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>
							<div class="col-md-4">

								<div class="form-group">
									<label for="is_active">Status</label>
									<select class="form-control form-control-sm" id="is_active" name="is_active">
										<option value="1" {{ old('is_active', $item->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
										<option value="0" {{ old('is_active', $item->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
									</select>
								</div>


							</div>
						</div>

						<div class="row">
							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-sm">
									<i class="fas fa-save"></i> Update Item
								</button>
								<a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
									Cancel
								</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection