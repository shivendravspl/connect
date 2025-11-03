@if($documents->isEmpty())
    <div class="text-center py-4">
        <i class="ri-file-search-line display-4 text-muted"></i>
        <p class="text-muted mt-2 small">No documents found for {{ $entityTypes[$entityType] ?? $entityType }}.</p>
    </div>
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Document Checklist for {{ $entityTypes[$entityType] ?? $entityType }}</h6>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="downloadExcel()">
                <i class="ri-download-line me-1"></i>Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="downloadPDF()">
                <i class="ri-file-pdf-line me-1"></i>PDF
            </button>
            {{--<button type="button" class="btn btn-sm btn-outline-success" onclick="saveChecklist()">
                <i class="ri-save-line me-1"></i>Save
            </button>--}}
        </div>
    </div>

    <div class="document-checklist-content">
        @foreach($documents as $category => $categoryDocuments)
            <div class="category-section mb-4">
                <h6 class="category-header bg-light p-2 rounded small fw-semibold mb-2">
                    <i class="ri-folder-line me-1"></i>{{ $category }}
                </h6>
                
                @php
                    $subCategories = $categoryDocuments->groupBy('sub_category');
                @endphp
                
                @foreach($subCategories as $subCategory => $subCategoryDocuments)
                    @if($subCategory)
                        <h7 class="sub-category-header ps-3 py-1 small fw-medium text-muted">
                            <i class="ri-subtract-line me-1"></i>{{ $subCategory }}
                        </h7>
                    @endif
                    
                    @php
                        $sortedDocuments = $subCategoryDocuments->sortBy(function($doc) {
                            return $doc->applicability == 'Mandatory' ? 0 : 
                                  ($doc->applicability == 'Optional' ? 1 : 2);
                        });
                    @endphp
                    
                    @foreach($sortedDocuments as $index => $document)
                        <div class="document-item border-bottom pb-2 mb-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge 
                                            @if($document->applicability == 'Mandatory') bg-danger
                                            @elseif($document->applicability == 'Optional') bg-warning text-dark
                                            @else bg-info
                                            @endif me-2" 
                                            style="font-size: 0.6rem; padding: 0.2rem 0.4rem;">
                                            {{ $document->applicability }}
                                        </span>
                                        <strong class="small">{{ $document->document_name }}</strong>
                                    </div>
                                    
                                    @if($document->checkpoints)
                                        <p class="small text-muted mb-1" style="font-size: 0.7rem; line-height: 1.3;">
                                            <strong>Checkpoints:</strong> {{ $document->checkpoints }}
                                        </p>
                                    @endif
                                    
                                    @if($document->applicability_justification)
                                        <p class="small text-muted mb-0" style="font-size: 0.7rem; line-height: 1.3;">
                                            <strong>Justification:</strong> {{ $document->applicability_justification }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @endforeach
    </div>

    <script>
    function downloadExcel() {
        const entityType = '{{ $entityType }}';
        window.location.href = `{{ route('document-checklist.download-excel') }}?entity_type=${entityType}`;
    }

    function downloadPDF() {
        const entityType = '{{ $entityType }}';
        window.location.href = `{{ route('document-checklist.download-pdf') }}?entity_type=${entityType}`;
    }

    function saveChecklist() {
        // Collect all document IDs
        const documentIds = []; 
        
        // You can implement checkboxes later to collect specific document IDs
        @foreach($documents as $category => $categoryDocuments)
            @foreach($categoryDocuments as $document)
                documentIds.push({{ $document->id }});
            @endforeach
        @endforeach
        
        fetch('{{ route("document-checklist.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                entity_type: '{{ $entityType }}',
                document_ids: documentIds,
                status: 'saved'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Checklist saved successfully!', 'success');
            } else {
                showToast('Error saving checklist', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error saving checklist', 'error');
        });
    }

    function showToast(message, type = 'info') {
        // You can use a proper toast library here
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    </script>

    <style>
    .document-checklist-content {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    .category-header {
        border-left: 3px solid #0d6efd;
        font-size: 0.8rem;
    }
    .sub-category-header {
        border-left: 2px solid #6c757d;
        font-size: 0.75rem;
    }
    .document-item:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    </style>
@endif