@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item">Form</li>
    <li class="breadcrumb-item active">{{ $page['studly_case'] }}</li>
@endpush

@push('page-title')
    {{ $page['studly_case'] }} Form
@endpush

@push('styles')
    <style>
        .el-box-text {
            position: relative;
            margin-bottom: 8px;
            padding: 8px;
            border: 1px dashed var(--vz-border-color);
            border-radius: 0.5rem;
            transition: all 0.3s;
            background-color: var(--vz-card-bg);
        }

        .el-box-text:hover {
            border-color: var(--vz-primary);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }

        .el-box-text .action-div {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
            gap: 5px;
        }

        .el-box-text:hover .action-div {
            display: flex;
        }

        .action-div button {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: none;
            background-color: var(--vz-light);
            color: var(--vz-secondary);
        }

        .action-div button:hover {
            background-color: var(--vz-primary);
            color: white;
        }

        .add-form-element {
            border: 1px dashed var(--vz-border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            background-color: var(--vz-card-bg);
            height: 100%;
        }

        .add-form-element:hover {
            border-color: var(--vz-primary);
        }

        .date-time-group {
            position: relative;
        }

        .date-time-group .input-suffix {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 4;
            pointer-events: none;
            color: var(--vz-secondary);
        }

        .js-ak-date-time-picker,
        .js-ak-date-picker,
        .js-ak-time-picker {
            padding-right: 2.5rem !important;
        }

        .offcanvas-form-builder {
            width: 400px;
            background-color: var(--vz-body-bg);
            color: var(--vz-body-color);
        }

        .form-builder-field {
            border-radius: 8px;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #e9ecef;
        }

        .form-builder-field:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-builder-field .field-icon {
            font-size: 24px;
            color: #405189;
            margin-bottom: 2px;
        }

        .form-builder-field h6 {
            font-size: 14px;
            margin-bottom: 4px;
            color: #343a40;
        }

        .form-builder-field p {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 0;
        }

        .card-sm {
            border-radius: 0.25rem;
            transition: all 0.2s;
        }

        .card-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .fs-12 {
            font-size: 12px;
        }

        .fs-14 {
            font-size: 14px;
        }

        .form-control-sm, .form-select-sm {
            min-height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }

        .dynamicForm .row {
            margin-bottom: 8px;
        }

        @media (max-width: 576px) {
            .offcanvas-form-builder {
                width: 100%;
            }

            .add-form-element {
                margin-bottom: 1rem;
            }
        }

       

        html, body {
            overflow-x: hidden !important; /* Prevent horizontal scroll on body */
            overflow-y: auto !important;   /* Keep vertical scroll */
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col">
            <div class="float-end">
                <button class="btn btn-success" id="generateForm">
                    <i class="ri-file-code-line align-middle me-1"></i> Generate Form
                </button>
                <button class="btn btn-warning" data-bs-toggle="offcanvas" data-bs-target="#ToolBar" aria-controls="ToolBar">
                    <i class="ri-settings-3-line align-middle me-1"></i> Form Builder
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="javascript:void(0);" enctype="multipart/form-data" class="form-page validate-form" novalidate>
                <div class="form-header mb-3">
                    <h4 class="card-title mb-0">Add New:</h4>
                </div>
                <div class="form-content dynamicForm">
                    @if ($forms_element != null)
                        @foreach ($forms_element as $input)
                            <div class="{{ $input->column_width }} el-box-text" data-id="{{ $input->id }}" data-sort-order="{{ $input->sorting_order }}">
                                <div class="input-container">
                                    <div class="input-label mb-2">
                                        <label for="{{ $input->column_name }}" class="form-label">{{ $input->column_title }}</label>
                                    </div>
                                    <div class="input-data">
                                        @if ($input->input_type == 'text' || $input->input_type == 'number' || $input->input_type == 'email')
                                            <input type="{{ $input->input_type }}" class="form-control" 
                                                   id="{{ $input->column_name }}" autocomplete="off"
                                                   name="{{ $input->column_name }}" placeholder="{{ $input->placeholder }}">
                                        @elseif($input->input_type == 'select' || $input->input_type == 'select2')
                                            <select id="{{ $input->column_name }}"
                                                    class="form-select {{ $input->input_type == 'select2' ? 'js-ak-select2' : '' }}"
                                                    name="{{ $input->column_name }}">
                                                <option value="">Select an option</option>
                                                <!-- Options should be dynamically populated -->
                                                  <option value="">Option 1</option>
                                                  <option value="">Option 2</option>
                                            </select>
                                        @elseif($input->input_type == 'textarea')
                                            <textarea class="form-control" id="{{ $input->column_name }}"
                                                      name="{{ $input->column_name }}"
                                                      placeholder="{{ $input->placeholder }}"></textarea>
                                        @elseif($input->input_type == 'date_time')
                                            <div class="group-input date-time-group"
                                                 id="ak_date_group_{{ $input->column_name }}">
                                                <input type="text" name="{{ $input->column_name }}" autocomplete="off"
                                                       id="{{ $input->column_name }}"
                                                       class="form-control {{ $input->column_type == 'date_time' ? 'js-ak-date-time-picker' : 'js-ak-date-picker' }}"
                                                       placeholder="{{ $input->placeholder }}">
                                                <div class="input-suffix js-ak-calendar-icon"
                                                     data-target="#{{ $input->column_name }}">
                                                    <i class="ri-calendar-line"></i>
                                                </div>
                                            </div>
                                        @elseif($input->input_type == 'time')
                                            <div class="group-input date-time-group"
                                                 id="ak_date_group_{{ $input->column_name }}">
                                                <input type="text" name="{{ $input->column_name }}" autocomplete="off"
                                                       class="form-control js-ak-time-picker" id="{{ $input->column_name }}"
                                                       placeholder="{{ $input->placeholder }}">
                                                <div class="input-suffix js-ak-time-icon"
                                                     data-target="#{{ $input->column_name }}">
                                                    <i class="ri-time-line"></i>
                                                </div>
                                            </div>
                                        @elseif($input->input_type == 'checkbox')
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="{{ $input->column_name }}" value="0">
                                                <input class="form-check-input" type="checkbox" id="{{ $input->column_name }}"
                                                       name="{{ $input->column_name }}" value="1">
                                                <label class="form-check-label" for="{{ $input->column_name }}"></label>
                                            </div>
                                        @elseif($input->input_type == 'multi-checkbox')
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="option1_{{ $input->column_name }}">
                                                <label class="form-check-label" for="option1_{{ $input->column_name }}">Option 1</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="option2_{{ $input->column_name }}">
                                                <label class="form-check-label" for="option2_{{ $input->column_name }}">Option 2</label>
                                            </div>
                                        @elseif($input->input_type == 'image_upload')
                                            <input type="file" class="form-control"
                                                   id="{{ $input->column_name }}" accept=".jpg,.jpeg,.png,.webp"
                                                   name="{{ $input->column_name }}"
                                                   data-file-type=".jpg,.jpeg,.png,.webp"
                                                   data-selected="Selected image for upload:">
                                            <div class="form-text">Allowed extensions: .jpg, .jpeg, .png, .webp. Recommended size: 1920x1080px.</div>
                                        @elseif($input->input_type == 'file_upload')
                                            <input type="file" class="form-control" id="{{ $input->column_name }}"
                                                   name="{{ $input->column_name }}">
                                        @endif
                                        @if($input->default_value)
                                            <div class="form-text">{{ $input->default_value }}</div>
                                        @endif
                                    </div>
                                    <div class="action-div">
                                        <button type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                                aria-controls="offcanvasRight" data-form_id="{{ $input->id }}"
                                                onclick="getFormDetails({{ $input->id }});">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <button class="delete-btn" type="button" data-form_id="{{ $input->id }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Form Builder Offcanvas -->
<div class="offcanvas offcanvas-end offcanvas-form-builder" tabindex="-1" id="ToolBar" aria-labelledby="ToolBarLabel">
    <div class="offcanvas-header border-bottom p-2">
        <h5 class="offcanvas-title fs-14" id="ToolBarLabel">
            <i class="ri-settings-3-line align-middle me-1"></i> Form Builder
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-2">
        <div class="row g-2">
            @php
                $fieldTypes = [
                    'text' => 'text',
                    'textarea' => 'file-text-line',
                    'number' => 'number-1',
                    'decimal' => 'number-2',
                    'email' => 'mail-line',
                    'date_time' => 'calendar-line',
                    'time' => 'time-line',
                    'select' => 'list-check',
                    'select2' => 'list-check-2',
                    'checkbox' => 'checkbox-line',
                    'file_upload' => 'upload-line',
                    'image_upload' => 'image-line'
                ];
            @endphp
            
            @foreach($fieldTypes as $type => $icon)
                <div class="col-6">
                    <div class="card card-sm h-100 cursor-pointer form-builder-field" onclick="prepareAddInput('{{ $type }}')">
                        <div class="card-body text-center p-1">
                            <i class="ri-{{ $icon }} fs-5 text-primary mb-1"></i>
                            <h6 class="mb-0 fs-12">{{ str_replace('_', ' ', ucfirst($type)) }}</h6>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

    <!-- Field Config Modal -->
<div class="modal fade" id="fieldConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light p-2">
                <h6 class="modal-title fs-14 mb-0">
                    <i class="ri-add-circle-line align-middle me-1"></i>
                    <span id="modalTitle">Add Field</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <form id="fieldConfigForm">
                    <input type="hidden" id="input_type">
                    <div class="mb-2">
                        <label class="form-label fs-12 mb-1">Name</label>
                        <input type="text" class="form-control form-control-sm" id="field_name" required>
                        <small class="text-muted">This will be used as both the label and field ID</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fs-12 mb-1">Placeholder (optional)</label>
                        <input type="text" class="form-control form-control-sm" id="field_placeholder">
                    </div>
                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_required1" name="is_required1" value="Y">
                            <label class="form-check-label fs-12" for="is_required1">Required</label>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fs-12 mb-1">Width</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(['col-12' => '100%', 'col-9' => '75%', 'col-6' => '50%', 'col-4' => '33%', 'col-3' => '25%'] as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="field_width" id="width_{{ $value }}" value="{{ $value }}" {{ $value == 'col-12' ? 'checked' : '' }}>
                                    <label class="form-check-label fs-12" for="width_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="confirmAddField()">
                    <i class="ri-add-line align-middle me-1"></i> Add Field
                </button>
            </div>
        </div>
     </div>
</div>

    <!-- Edit Field Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-form-builder" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header border-bottom p-2">
            <h6 class="offcanvas-title fs-14 mb-0" id="offcanvasRightLabel">
                <i class="ri-pencil-line align-middle me-1"></i>
                <span id="input_type_label">Field Settings</span>
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-2">
            <form id="updateFormElement" method="POST">
                @csrf
                <input type="hidden" id="form_id" name="form_id">
                <input type="hidden" id="input_type" name="input_type">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Label</label>
                            <input type="text" class="form-control form-control-sm" id="label_name" name="label_name" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Column Name</label>
                            <input type="text" class="form-control form-control-sm" id="column_name" name="column_name" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Placeholder</label>
                            <input type="text" class="form-control form-control-sm" id="placeholder" name="placeholder">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Default Value</label>
                            <input type="text" class="form-control form-control-sm" id="default_value" name="default_value">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fs-12 mb-1">Width</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(['col-12' => '100%', 'col-9' => '75%', 'col-6' => '50%', 'col-4' => '33%', 'col-3' => '25%'] as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="width" id="edit_width_{{ $value }}" value="{{ $value }}">
                                    <label class="form-check-label fs-12" for="edit_width_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="hstack gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="Y">
                                <label class="form-check-label fs-12" for="is_required">Required</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_unique" name="is_unique" value="Y">
                                <label class="form-check-label fs-12" for="is_unique">Unique</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_nullable" name="is_nullable" value="Y">
                                <label class="form-check-label fs-12" for="is_nullable">Nullable</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="typeDiv">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Type</label>
                            <select name="column_type" id="column_type" class="form-select form-select-sm">
                                <option value="">Select</option>
                                <option value="date">Date</option>
                                <option value="date_time">Date & Time</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="sourceDiv">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Source Table</label>
                            <select name="source_table" id="source_table" class="form-select form-select-sm">
                                <option value="">Select Source</option>
                                @if ($source_table)
                                    @foreach ($source_table as $key => $value)
                                        <option value="{{ $value }}">{{ $value }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 d-none" id="keyDiv">
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="form-label fs-12 mb-1">Key</label>
                                <select name="source_table_key" id="source_table_key" class="form-select form-select-sm"></select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="form-label fs-12 mb-1">Value</label>
                                <select name="source_table_value" id="source_table_value" class="form-select form-select-sm"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="switchDiv">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_switch" name="is_switch" value="Y">
                            <label class="form-check-label fs-12" for="is_switch">Display as switch</label>
                        </div>
                    </div>
                    <div class="col-12 d-none" id="columnLengthDiv">
                        <div class="mb-2">
                            <label class="form-label fs-12 mb-1">Column Length</label>
                            <input type="text" class="form-control form-control-sm" id="column_length" name="column_length">
                        </div>
                    </div>
                    <div class="row g-2 d-none" id="minMaxDiv">
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="form-label fs-12 mb-1">Min Value</label>
                                <input type="text" class="form-control form-control-sm" id="min_value" name="min_value">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="form-label fs-12 mb-1">Max Value</label>
                                <input type="text" class="form-control form-control-sm" id="max_value" name="max_value">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="ri-save-line align-middle me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <input type="hidden" name="page_id" id="page_id" value="{{ $page['id'] }}">
    <input type="hidden" name="page_name" id="page_name" value="{{ $page['page_name'] }}">
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush

@push('scripts')
    <script>
        // Reorganize fields for proper row layout
        function reorganizeFields() {
    const $dynamicForm = $('.dynamicForm');
    const $fields = $dynamicForm.find('.el-box-text').detach();
    let $currentRow = $('<div class="row g-2 mb-2"></div>'); // Add margin for spacing
    let currentRowWidth = 0;

    if ($fields.length === 0) {
        $dynamicForm.empty();
        return;
    }

    $fields.each(function() {
        const $field = $(this);
        const widthClass = $field.attr('class').match(/col-\d+/) ? $field.attr('class').match(/col-\d+/)[0] : 'col-12';
        const width = parseInt(widthClass.replace('col-', '')) || 12;

        if (currentRowWidth + width <= 12) {
            $currentRow.append($field);
            currentRowWidth += width;
        } else {
            $dynamicForm.append($currentRow);
            $currentRow = $('<div class="row g-2 mb-2"></div>').append($field);
            currentRowWidth = width;
        }
    });

    if ($currentRow.children().length > 0) {
        $dynamicForm.append($currentRow);
    }

    // Ensure dynamicForm is scrollable
    $dynamicForm.css({
    'overflow-x': 'hidden',
    'overflow-y': 'auto',
        'max-width': '100%'
    });
}

        $(document).ready(function () {
            // Initialize sortable
            $(".dynamicForm").sortable({
            items: ".el-box-text",
            cursor: "move",
            opacity: 0.7,
            placeholder: "sortable-placeholder",
            tolerance: "pointer",
            containment: "parent", // Restrict dragging to parent container
            scroll: true, // Enable scrolling while dragging
            update: function (event, ui) {
                const sortedIDs = $(this).sortable("toArray", { attribute: "data-id" });
                $.ajax({
                    url: "{{ route('update_sorting_order') }}",
                    method: 'POST',
                    data: { 
                        order: sortedIDs,
                        page_id: $("#page_id").val(),
                        page_name: $("#page_name").val()
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        $('body').css('overflow', ''); // Reset body overflow
                        reorganizeFields();
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.toast_error || 'Sorting update failed'
                        });
                        $(".dynamicForm").sortable("cancel");
                    }
                });
            }
        }).disableSelection();

            // Initialize Select2 for dropdowns
            $('.js-ak-select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });

            // Initialize date/time pickers (assuming Flatpickr or similar)
            $('.js-ak-date-picker').flatpickr({
                dateFormat: 'Y-m-d'
            });
            $('.js-ak-date-time-picker').flatpickr({
                enableTime: true,
                dateFormat: 'Y-m-d H:i'
            });
            $('.js-ak-time-picker').flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i'
            });

            // Initialize form order
            initializeFormOrder();

            // Handle modal/offcanvas cleanup
            $('#fieldConfigModal').on('hidden.bs.modal', function () {
                $('body').removeClass('modal-open').css('overflow', '');
                $('.modal-backdrop').remove();
            });

            $('#ToolBar, #offcanvasRight').on('hidden.bs.offcanvas', function () {
                $('body').removeClass('offcanvas-open modal-open').css('overflow', '');
                $('.offcanvas-backdrop').remove();
            });

            $(document).ajaxComplete(function () {
                $('body').css('overflow', '');
            });
        });

        function initializeFormOrder() {
            const $dynamicForm = $('.dynamicForm');
            const $fields = $dynamicForm.find('.el-box-text').detach();
            
            $fields.sort(function(a, b) {
                return $(a).data('sort-order') - $(b).data('sort-order');
            });
            
            $dynamicForm.append($fields);
            reorganizeFields();
        }

      function prepareAddInput(type) {
    const modal = new bootstrap.Modal('#fieldConfigModal');
    const form = $('#fieldConfigForm')[0];
    form.reset();
    
    $('#input_type').val(type);
    const defaultName = type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ');
    
    // Set default name and let placeholder be empty by default
    //$('#field_name').val(defaultName);
    $('#field_placeholder').val(''); // Empty by default
    
    // Update modal title
    $('#modalTitle').text(`Add ${defaultName} Field`);
    
    modal.show();
}

function confirmAddField() {
    const form = $('#fieldConfigForm');
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }
    
    const type = $('#input_type').val();
    const name = $('#field_name').val();
    const placeholder = $('#field_placeholder').val();
    const width = $('input[name="field_width"]:checked').val();
    const isRequired = $('#is_required1').is(':checked') ? 'Y' : 'N';
    const label = name;
    const button = $('#fieldConfigForm').find('button[type="button"].btn-primary');
    const originalText = button.html();
    button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
    button.prop('disabled', true);

    $.ajax({
        url: "{{ route('add_form_element') }}",
        type: 'POST',
        data: {
            page_id: $("#page_id").val(),
            page_name: $("#page_name").val(),
            type: type,
            name: convertToFieldName(name),
            placeholder: label ,
            column_width: width,
            is_required: isRequired
        },
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (data) {
            button.html(originalText);
            button.prop('disabled', false);
            if (data.status === 200) {
                window.location.reload();
            } else {
                Swal.fire('Error', data.message || 'Failed to add field', 'error');
            }
        },
        error: function(xhr) {
            button.html(originalText);
            button.prop('disabled', false);
            if (xhr.status === 400) {
                Swal.fire('Error', xhr.responseJSON.message || 'Field already exists', 'error');
            } else {
                Swal.fire('Error', 'An error occurred while adding the field', 'error');
            }
        }
    });
}

function convertToFieldName(str) {
    return str.trim()
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '');
}

       $(document).on('click', '#generateForm', function () {
    const button = $(this);
    const originalText = button.html();
    button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
    button.prop('disabled', true);

    $.ajax({
        url: "{{ route('generate_form') }}",
        type: 'POST',
        data: {
            page_id: $("#page_id").val(),
            page_name: $("#page_name").val(),
        },
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (data) {
            button.html(originalText);
            button.prop('disabled', false);
            if (data.status == 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Form Generated Successfully',
                    timer: 2000
                }).then(() => {
                    $('html, body').css('overflow', 'auto');
                    $('.dynamicForm').css('overflow', 'auto');
                    reorganizeFields();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Something went wrong!'
                });
            }
        },
        error: function() {
            button.html(originalText);
            button.prop('disabled', false);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while generating the form'
            });
        }
    });
});
        $('#updateFormElement').on('submit', function (e) {
            e.preventDefault();
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            const formData = $(this).serialize();
            const button = $(this).find('button[type="submit"]');
            const originalText = button.html();
            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            button.prop('disabled', true);

            $.ajax({
                url: "{{ route('form_element_update') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    button.html(originalText);
                    button.prop('disabled', false);
                    if (data.status == 200) {
                        const $field = $(`.el-box-text[data-id="${data.form_id}"]`);
                        $field.find('.input-label label').text(data.column_title);
                        $field.find('.input-data input, .input-data textarea, .input-data select').attr({
                            id: data.column_name,
                            name: data.column_name,
                            placeholder: data.placeholder || ''
                        });
                        $field.removeClass(function (index, className) {
                            return (className.match(/(^|\s)col-\S+/g) || []).join(' ');
                        }).addClass(data.new_width);
                        $field.find('.input-data .form-text').remove();
                        if (data.default_value) {
                            $field.find('.input-data').append(`<div class="form-text">${data.default_value}</div>`);
                        }

                        reorganizeFields();
                        bootstrap.Offcanvas.getInstance('#offcanvasRight').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                },
                error: function () {
                    button.html(originalText);
                    button.prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the form'
                    });
                }
            });
        });

        function getFormDetails(form_id) {
            $.ajax({
                url: "{{ route('get_form_element_details') }}",
                type: 'POST',
                data: { form_id: form_id },
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    if (data.status == 200) {
                        const elementDetails = data.data;
                        $("#form_id").val(form_id);
                        $("#input_type").val(elementDetails.input_type);
                        $("#input_type_label").html(`Settings For: ${elementDetails.column_title}`);
                        $("#label_name").val(elementDetails.column_title);
                        $("#column_name").val(elementDetails.column_name);
                        $("#placeholder").val(elementDetails.placeholder);
                        $("#default_value").val(elementDetails.default_value);
                        $(':radio[name="width"]').prop('checked', false);
                        $(`:radio[name="width"][value="${elementDetails.column_width}"]`).prop('checked', true);

                        // Conditional fields
                        $("#typeDiv").toggleClass('d-none', elementDetails.input_type !== 'date_time');
                        $("#column_type").val(elementDetails.column_type || '');

                        $("#sourceDiv, #keyDiv").toggleClass('d-none', !['select', 'select2'].includes(elementDetails.input_type));
                        if (['select', 'select2'].includes(elementDetails.input_type)) {
                            $("#source_table").val(elementDetails.source_table);
                            getSourceTableColumn(elementDetails.source_table, () => {
                                $("#source_table_key").val(elementDetails.source_table_column_key);
                                $("#source_table_value").val(elementDetails.source_table_column_value);
                            });
                        }

                        $("#switchDiv").toggleClass('d-none', elementDetails.input_type !== 'checkbox');
                        $("#columnLengthDiv").toggleClass('d-none', !['email', 'text'].includes(elementDetails.input_type));
                        $("#column_length").val(elementDetails.column_length || '');

                        $("#minMaxDiv").toggleClass('d-none', !['number', 'decimal'].includes(elementDetails.input_type));
                        $("#min_value").val(elementDetails.min_value || '');
                        $("#max_value").val(elementDetails.max_value || '');

                        setCheckboxProperties('is_required', elementDetails.is_required);
                        setCheckboxProperties('is_unique', elementDetails.is_unique);
                        setCheckboxProperties('is_nullable', elementDetails.is_nullable);
                        setCheckboxProperties('is_switch', elementDetails.is_switch);

                        new bootstrap.Offcanvas('#offcanvasRight').show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to fetch form details'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while fetching form details'
                    });
                }
            });
        }

        function setCheckboxProperties(elementId, elementValue) {
            const isChecked = elementValue === 'Y';
            $(`#${elementId}`).prop('checked', isChecked).val(isChecked ? 'Y' : 'N');
        }

        function getSourceTableColumn(table_name, callback) {
            if (!table_name) {
                $("#source_table_key, #source_table_value").empty();
                if (callback) callback();
                return;
            }
            $.ajax({
                url: "{{ route('get_source_table_columns') }}",
                type: 'POST',
                data: { source_table: table_name },
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    if (data.status === 200) {
                        $("#source_table_key, #source_table_value").empty().append('<option value="">Select</option>');
                        $.each(data.column_list, function (key, value) {
                            $('#source_table_key, #source_table_value').append($('<option>', { value: key, text: value }));
                        });
                        if (callback) callback();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load table columns'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load table columns'
                    });
                }
            });
        }

        $(document).on("change", "#source_table", function () {
            getSourceTableColumn($(this).val());
        });

        $(document).on("click", ".delete-btn", function () {
    const button = $(this);
    const form_id = button.data('form_id');

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            button.prop('disabled', true);

            $.ajax({
                url: "{{ route('form_element_delete') }}",
                type: 'POST',
                data: { form_id: form_id },
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    if (data.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: data.msg, // Show the server message
                            timer: 2000
                        }).then(() => {
                                window.location.reload();
                        });
                    } else {
                        button.html('<i class="ri-delete-bin-line"></i>');
                        button.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Something went wrong!',
                            footer: data.error ? 'Technical details: ' + data.error : ''
                        });
                    }
                },
                error: function (xhr) {
                    button.html('<i class="ri-delete-bin-line"></i>');
                    button.prop('disabled', false);
                    
                    let errorMsg = 'An error occurred while deleting!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        footer: xhr.responseJSON && xhr.responseJSON.error ? 
                               'Technical details: ' + xhr.responseJSON.error : ''
                    });
                }
            });
        }
    });
});
    </script>
@endpush