@php
    // Initialize declarations data
    $declarationsData = old('declarations', []);

    // If editing existing application
    if (isset($application->declarations) && $application->declarations->count() > 0) {
        $declarationsData = $application->declarations->mapWithKeys(function ($declaration) {
            $data = [
                'has_issue' => $declaration->has_issue ? '1' : '0',
            ];
            // Handle details: already an array or null, no need to decode
            $details = is_array($declaration->details) ? $declaration->details : ($declaration->details ? json_decode($declaration->details, true) : []);
            return [$declaration->question_key => array_merge($data, is_array($details) ? $details : [])];
        })->toArray();
    }

    // Default values for all questions
    $defaultDeclarations = [
        'is_other_distributor' => ['has_issue' => '0', 'other_distributor_details' => ''],
        'has_sister_concern' => ['has_issue' => '0', 'sister_concern_details' => ''],
        'has_question_c' => ['has_issue' => '0', 'question_c_details' => ''],
        'has_question_d' => ['has_issue' => '0', 'question_d_details' => ''],
        'has_question_e' => ['has_issue' => '0', 'question_e_details' => ''],
        'has_disputed_dues' => [
            'has_issue' => '0',
            'disputed_amount' => '',
            'dispute_nature' => '',
            'dispute_year' => '',
            'dispute_status' => '',
            'dispute_reason' => '',
        ],
        'has_question_g' => ['has_issue' => '0', 'question_g_details' => ''],
        'has_question_h' => ['has_issue' => '0', 'question_h_details' => ''],
        'has_question_i' => ['has_issue' => '0', 'question_i_details' => ''],
        'has_question_j' => [
            'has_issue' => '0',
            'referrer_1' => '',
            'referrer_2' => '',
            'referrer_3' => '',
            'referrer_4' => '',
        ],
        'has_question_k' => ['has_issue' => '0', 'question_k_details' => ''],
        'has_question_l' => ['has_issue' => '0', 'question_l_details' => ''],
        'declaration_truthful' => ['has_issue' => '0'],
        'declaration_update' => ['has_issue' => '0'],
    ];

    // Merge defaults with existing data or old input, ensuring all keys are present
    foreach ($defaultDeclarations as $key => $defaults) {
        $declarationsData[$key] = array_merge(
            $defaults,
            isset($declarationsData[$key]) ? $declarationsData[$key] : []
        );
        // Override has_issue only if details contain non-empty values
        if (!in_array($key, ['declaration_truthful', 'declaration_update'])) {
            $details = array_diff_key($declarationsData[$key], ['has_issue' => '']);
            $hasNonEmptyDetails = false;
            foreach ($details as $value) {
                if (!empty($value)) {
                    $hasNonEmptyDetails = true;
                    break;
                }
            }
            if ($hasNonEmptyDetails) {
                $declarationsData[$key]['has_issue'] = '1';
            }
        }
    }

    // Define questions for rendering
    $questions = [
        'is_other_distributor' => [
            'label' => 'a. Whether the Distributor is an Agent/Distributor of any other Company?',
            'details_field' => 'other_distributor_details',
        ],
        'has_sister_concern' => [
            'label' => 'b. Whether the Distributor has any sister concern or affiliated entity other than the one applying for this distributorship?',
            'details_field' => 'sister_concern_details',
        ],
        'has_question_c' => [
            'label' => 'c. Whether the Distributor is acting as an Agent/Distributor for any other entities in the distribution of similar crops?',
            'details_field' => 'question_c_details',
        ],
        'has_question_d' => [
            'label' => 'd. Whether the Distributor is a partner, relative, or otherwise associated with any entity engaged in the business of agro inputs?',
            'details_field' => 'question_d_details',
        ],
        'has_question_e' => [
            'label' => 'e. Whether the Distributor has previously acted as an Agent/Distributor of VNR Seeds and is again applying for a Distributorship?',
            'details_field' => 'question_e_details',
        ],
        'has_disputed_dues' => [
            'label' => 'f. Whether any disputed dues are payable by the Distributor to the other Company/Bank/Financial Institution?',
            'details_fields' => [
                'disputed_amount' => 'Amount',
                'dispute_nature' => 'Nature of Dispute',
                'dispute_year' => 'Year of Dispute',
                'dispute_status' => 'Present Position',
                'dispute_reason' => 'Reason for Default',
            ],
        ],
        'has_question_g' => [
            'label' => 'g. Whether the Distributor has ceased to be Agent/Distributor of any other company in the last twelve months?',
            'details_field' => 'question_g_details',
        ],
        'has_question_h' => [
            'label' => 'h. Whether the Distributor’s relative is connected in any way with VNR Seeds and any other Seed Company?',
            'details_field' => 'question_h_details',
        ],
        'has_question_i' => [
            'label' => 'i. Whether the Distributor is involved in any other capacity with the Company apart from this application?',
            'details_field' => 'question_i_details',
        ],
        'has_question_j' => [
            'label' => 'j. Whether the Distributor has been referred by any Distributors or other parties associated with the Company?',
            'details_fields' => [
                'referrer_1' => 'Referrer I',
                'referrer_2' => 'Referrer II',
                'referrer_3' => 'Referrer III',
                'referrer_4' => 'Referrer IV',
            ],
        ],
        'has_question_k' => [
            'label' => 'k. Whether the Distributor is currently marketing or selling products under its own brand name?',
            'details_field' => 'question_k_details',
        ],
        'has_question_l' => [
            'label' => 'l. Whether the Distributor has been employed in the agro-input industry at any point during the past 5 years?',
            'details_field' => 'question_l_details',
        ],
    ];
@endphp

<div id="declarations" class="form-section step-content" data-step="8" style="display: none;">
    <h5 class="mb-4">Declarations</h5>
    <p class="text-muted mb-4">Answer the following questions carefully (Select Yes/No, if Yes then specify details)</p>

    @foreach($questions as $questionKey => $config)
        <x-declaration-question
            :questionKey="$questionKey"
            :label="$config['label']"
            :detailsField="isset($config['details_field']) ? $config['details_field'] : null"
            :detailsFields="isset($config['details_fields']) ? $config['details_fields'] : []"
            :declarationsData="$declarationsData"
        />
    @endforeach

    <!-- Declaration checkboxes -->
    <div class="card">
        <div class="card-body">
            <h6 class="mb-3">Declaration</h6>
            <div class="form-group mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="declaration_truthful" name="declaration_truthful" value="1" required {{ old('declaration_truthful', $declarationsData['declaration_truthful']['has_issue']) == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="declaration_truthful">
                    a. I/We hereby solemnly affirm the truthfulness and completeness of the foregoing information and agree to be bound by all terms and conditions of the appointment/agreement with the Company.
                </label>
            </div>
            <div class="form-group mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="declaration_update" name="declaration_update" value="1" required {{ old('declaration_update', $declarationsData['declaration_update']['has_issue']) == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="declaration_update">
                    b. I/We undertake to inform the company of any changes to the information provided herein within a period of 7 days, accompanied by relevant documentation.
                </label>
            </div>
        </div>
    </div>

    <!-- Form Filled By Section -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="mb-3">Form Filled By</h6>
            <div class="form-group mb-3">
                <label class="form-label">Name</label>
                <p class="form-control-plaintext">{{ Auth::user()->name }}</p>
            </div>
        </div>
    </div>


    <!-- Hidden inputs -->
    <input type="hidden" name="current_step" value="8">
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle details container based on radio button selection
    function toggleDetails(radioName, containerId) {
        $(`input[name="${radioName}"]`).on('change', function() {
            const showDetails = $(this).val() === '1';
            $(containerId).toggle(showDetails);
            if (!showDetails) {
                $(containerId).find('input, textarea').val('').prop('disabled', true);
            } else {
                $(containerId).find('input, textarea').prop('disabled', false);
            }
        });

        // Initialize visibility and disabled state based on current selection
        const selectedValue = $(`input[name="${radioName}"]:checked`).val();
        $(containerId).toggle(selectedValue === '1');
        $(containerId).find('input, textarea').prop('disabled', selectedValue !== '1');
    }

    // Set up toggle for all Yes/No questions
    const questions = [
        @foreach($questions as $questionKey => $config)
            { radioName: '{{ $questionKey }}', containerId: '#{{ $questionKey }}_details_container' },
        @endforeach
    ];

    questions.forEach(question => {
        toggleDetails(question.radioName, question.containerId);
    });

    // Clear disabled fields before form submission to prevent sending unnecessary data
    $('#distributorForm').on('submit', function() {
        questions.forEach(question => {
            const selectedValue = $(`input[name="${question.radioName}"]:checked`).val();
            if (selectedValue !== '1') {
                $(question.containerId).find('input, textarea').val('');
            }
        });
    });
});
</script>
@endpush