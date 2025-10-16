@if($documents->isEmpty())
    <div class="text-center py-4">
        <i class="ri-file-search-line display-4 text-muted"></i>
        <p class="text-muted mt-2 small">No documents found for {{ $entityTypes[$entityType] ?? $entityType }}.</p>
    </div>
@else
    <div class="document-checklist-content">
        @foreach($documents as $category => $categoryDocuments)
            <div class="category-section mb-4">
                <h6 class="category-header bg-light p-2 rounded small fw-semibold mb-2">
                    <i class="ri-folder-line me-1"></i>{{ $category }}
                </h6>
                
                {{-- Sort documents: Mandatory first, then others --}}
                @php
                    $sortedDocuments = $categoryDocuments->sortBy(function($doc) {
                        return $doc->applicability == 'Mandatory' ? 0 : 1;
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
                                @if($document->description)
                                    <p class="small text-muted mb-0" style="font-size: 0.7rem; line-height: 1.3;">
                                        {{ $document->description }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <style>
    .document-checklist-content {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    .category-header {
        border-left: 3px solid #0d6efd;
        font-size: 0.8rem;
    }
    .document-item:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    </style>
@endif