@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Add New Indent Item</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('indents.save-items', $indent->id) }}" method="POST" id="itemForm">
                        @csrf
                        <div class="form-group">
                            <label>Item Name</label>
                            <select class="form-control item-select" name="items[0][item_id]" required>
                                <option value="">Select Item</option>
                                @foreach($itemGroups as $group)
                                    <optgroup label="{{ $group->name }}">
                                        @foreach($group->items as $item)
                                            <option value="{{ $item->id }}" data-uom="{{ $item->uom }}">
                                                {{ $item->name }} ({{ $item->code }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="items[0][quantity]" required>
                        </div>
                        <div class="form-group">
                            <label>Unit</label>
                            <input type="text" class="form-control uom" readonly>
                        </div>
                        <div class="form-group">
                            <label>Required Date</label>
                            <input type="date" class="form-control" name="items[0][required_date]" required>
                        </div>
                        <div class="form-group">
                            <label>Specification</label>
                            <input type="text" class="form-control" name="items[0][specification]">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Items</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- This will be handled in the index view after submission -->
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.querySelector('.item-select');
    const uomField = document.querySelector('.uom');

    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        uomField.value = selectedOption.dataset.uom || '';
    });
});
</script>
@endpush
@endsection