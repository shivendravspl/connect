@if($documents->isEmpty())
    <div class="text-center py-4">
        <i class="ri-file-search-line display-4 text-muted"></i>
        <p class="text-muted mt-2 small">No documents found for {{ $entityTypes[$entityType] ?? $entityType }}.</p>
    </div>
@else
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">Document Checklist</h5>
            <p class="text-muted small mb-0">{{ $entityTypes[$entityType] ?? $entityType }}</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadExcel()" 
                    title="Download as Excel (Table Format)">
                <i class="ri-download-line me-1"></i>Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="downloadPDF()" 
                    title="Download as PDF (Table Format)">
                <i class="ri-file-pdf-line me-1"></i>PDF
            </button>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="row g-2 mb-4">
        @php
            $allDocuments = $documents->flatten();
            $mandatoryCount = $allDocuments->where('applicability', 'Mandatory')->count();
            $optionalCount = $allDocuments->where('applicability', 'Optional')->count();
            $conditionalCount = $allDocuments->where('applicability', 'Conditional')->count();
            $totalCount = $allDocuments->count();
        @endphp
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="text-danger mb-1">{{ $mandatoryCount }}</h4>
                    <small class="text-muted">Mandatory</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="text-warning mb-1">{{ $optionalCount }}</h4>
                    <small class="text-muted">Optional</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="text-info mb-1">{{ $conditionalCount }}</h4>
                    <small class="text-muted">Conditional</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="text-success mb-1">{{ $totalCount }}</h4>
                    <small class="text-muted">Total Documents</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Accordion Layout -->
    <div class="accordion document-checklist-content" id="documentAccordion">
        @foreach($documents as $category => $categoryDocuments)
            @php
                $categorySlug = \Illuminate\Support\Str::slug($category);
            @endphp
            
            <div class="accordion-item border-0 mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed shadow-none" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapse{{ $categorySlug }}" aria-expanded="false" 
                            aria-controls="collapse{{ $categorySlug }}">
                        <div class="d-flex align-items-center w-100">
                            <i class="ri-folder-2-line text-primary me-3"></i>
                            <div class="flex-grow-1 text-start">
                                <strong class="text-dark">{{ $category }}</strong>
                                <small class="text-muted ms-2">
                                    ({{ $categoryDocuments->count() }} documents)
                                </small>
                            </div>
                            <div class="flex-shrink-0">
                                @if($category == 'Address Proof')
                                    <span class="badge bg-info">Conditional</span>
                                @elseif($category == 'Credit Worthiness')
                                    <span class="badge bg-warning text-dark">Any One Required</span>
                                @elseif($category == 'Declarations')
                                    <span class="badge bg-secondary">In Agreement</span>
                                @endif
                            </div>
                        </div>
                    </button>
                </h2>
                
                <div id="collapse{{ $categorySlug }}" class="accordion-collapse collapse" 
                     data-bs-parent="#documentAccordion">
                    <div class="accordion-body p-0">
                        @foreach($categoryDocuments->groupBy('sub_category') as $subCategory => $subDocs)
                            @if($subCategory)
                                <div class="subcategory-header bg-light px-4 py-2 border-bottom">
                                    <h6 class="mb-0 small fw-semibold text-dark">
                                        <i class="ri-subtract-line me-2 text-muted"></i>{{ $subCategory }}
                                    </h6>
                                </div>
                            @endif
                            
                            @php
                                $sortedDocuments = $subDocs->sortBy(function($doc) {
                                    return $doc->applicability == 'Mandatory' ? 0 : 
                                          ($doc->applicability == 'Optional' ? 1 : 2);
                                });
                            @endphp
                            
                            @foreach($sortedDocuments as $document)
                                <div class="document-item px-4 py-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <!-- Status Indicator -->
                                        <div class="document-status me-3 flex-shrink-0">
                                            @if($document->applicability == 'Mandatory')
                                                <div class="mandatory-indicator bg-danger rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 24px; height: 24px;" title="Mandatory Document">
                                                    <i class="ri-alert-line text-white" style="font-size: 12px;"></i>
                                                </div>
                                            @elseif($document->applicability == 'Optional')
                                                <div class="optional-indicator bg-warning rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 24px; height: 24px;" title="Optional Document">
                                                    <i class="ri-information-line text-dark" style="font-size: 12px;"></i>
                                                </div>
                                            @else
                                                <div class="conditional-indicator bg-info rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 24px; height: 24px;" title="Conditional Document">
                                                    <i class="ri-checkbox-circle-line text-white" style="font-size: 12px;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Document Content -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <h6 class="mb-0 me-2">{{ $document->document_name }}</h6>
                                                <span class="badge 
                                                    @if($document->applicability == 'Mandatory') bg-danger
                                                    @elseif($document->applicability == 'Optional') bg-warning text-dark
                                                    @else bg-info
                                                    @endif small">
                                                    {{ $document->applicability }}
                                                </span>
                                            </div>
                                            
                                            @if($document->checkpoints)
                                                <div class="checkpoints mb-2">
                                                    <strong class="small text-muted d-block mb-1">Checkpoints:</strong>
                                                    <p class="small text-dark mb-0" style="line-height: 1.4;">{{ $document->checkpoints }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($document->applicability_justification)
                                                <div class="justification">
                                                    <strong class="small text-muted d-block mb-1">Notes:</strong>
                                                    <p class="small text-dark mb-0" style="line-height: 1.4;">{{ $document->applicability_justification }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
.document-checklist-content {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #dee2e6;
}

.document-item {
    transition: background-color 0.2s ease;
}

.document-item:hover {
    background-color: #f8f9fa;
}

.subcategory-header {
    background-color: #f8f9fa !important;
}

/* Custom scrollbar */
.document-checklist-content::-webkit-scrollbar {
    width: 6px;
}

.document-checklist-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.document-checklist-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.document-checklist-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Ensure proper spacing */
.accordion-body {
    background-color: #fff;
}

.document-item:last-child {
    border-bottom: none !important;
}
</style>

<script>
function downloadExcel() {
    const entityType = '{{ $entityType }}';
    const url = `{{ route('document-checklist.download-excel') }}?entity_type=${encodeURIComponent(entityType)}`;
    window.location.href = url;
}

function downloadPDF() {
    const entityType = '{{ $entityType }}';
    const url = `{{ route('document-checklist.download-pdf') }}?entity_type=${encodeURIComponent(entityType)}`;
    window.location.href = url;
}

// Initialize tooltips and enhance UX
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-expand first category if only one exists
    const accordionItems = document.querySelectorAll('.accordion-item');
    if (accordionItems.length === 1) {
        const firstButton = accordionItems[0].querySelector('.accordion-button');
        if (firstButton) {
            firstButton.click();
        }
    }
    
    // Add click tracking for analytics (optional)
    document.querySelectorAll('.accordion-button').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.querySelector('strong').textContent;
            console.log('Category opened:', category);
        });
    });
});

// Optional: Add keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Collapse all accordions when Escape is pressed
        document.querySelectorAll('.accordion-button:not(.collapsed)').forEach(button => {
            button.click();
        });
    }
});
</script>