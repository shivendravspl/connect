@props([
    'questionKey' => '',
    'label' => '',
    'detailsField' => null,
    'detailsFields' => [],
    'declarationsData' => [],
])

<div class="card mb-4">
    <div class="card-body">
        <div class="form-group mb-3">
            <label class="form-label">{!! $label !!}</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="{{ $questionKey }}" id="{{ $questionKey }}_yes" value="1" required {{ old($questionKey, $declarationsData[$questionKey]['has_issue'] ?? '0') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $questionKey }}_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="{{ $questionKey }}" id="{{ $questionKey }}_no" value="0" {{ old($questionKey, $declarationsData[$questionKey]['has_issue'] ?? '0') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $questionKey }}_no">No</label>
            </div>
            <div class="invalid-feedback text-danger">Please select Yes or No.</div>

            @if($detailsField)
                <div class="form-group mt-2" id="{{ $questionKey }}_details_container" style="display: {{ old($questionKey, $declarationsData[$questionKey]['has_issue'] ?? '0') == '1' ? 'block' : 'none' }};">
                    <label for="{{ $detailsField }}" class="form-label">Please specify:</label>
                    <textarea class="form-control" id="{{ $detailsField }}" name="{{ $detailsField }}" rows="2">{{ old($detailsField, $declarationsData[$questionKey][$detailsField] ?? '') }}</textarea>
                </div>
            @elseif($detailsFields)
                <div class="form-group mt-2" id="{{ $questionKey }}_details_container" style="display: {{ old($questionKey, $declarationsData[$questionKey]['has_issue'] ?? '0') == '1' ? 'block' : 'none' }};">
                    <div class="row">
                        @foreach($detailsFields as $field => $label)
                            <div class="{{ in_array($field, ['disputed_amount', 'dispute_nature', 'referrer_1', 'referrer_2', 'referrer_3', 'referrer_4']) ? 'col-md-6' : 'col-md-4' }}">
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                <input type="text" class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $declarationsData[$questionKey][$field] ?? '') }}">
                            </div>
                            @if(in_array($field, ['disputed_amount', 'referrer_2']))
                                </div><div class="row mt-2">
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>