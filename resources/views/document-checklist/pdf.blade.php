<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Checklist - {{ $entityTypeName }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .category { margin-bottom: 15px; break-inside: avoid; }
        .category-header { background-color: #f8f9fa; padding: 8px; font-weight: bold; border-left: 4px solid #0d6efd; margin-bottom: 8px; }
        .sub-category { margin-left: 15px; margin-bottom: 5px; font-style: italic; color: #666; font-size: 11px; }
        .document-item { margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #eee; break-inside: avoid; }
        .document-name { font-weight: bold; margin-bottom: 3px; font-size: 11px; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 9px; border-radius: 3px; margin-right: 5px; }
        .badge-mandatory { background-color: #dc3545; color: white; }
        .badge-optional { background-color: #ffc107; color: black; }
        .badge-on-applicability { background-color: #0dcaf0; color: black; }
        .checkpoint, .justification { font-size: 10px; color: #666; margin-bottom: 2px; }
        .page-break { page-break-after: always; }
        .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Document Checklist</h1>
        <h2>Entity Type: {{ $entityTypeName }}</h2>
        <p>Generated on: {{ date('F j, Y') }}</p>
    </div>

    @foreach($documents as $category => $categoryDocuments)
        <div class="category">
            <div class="category-header">{{ $category }}</div>
            
            @php
                $subCategories = $categoryDocuments->groupBy('sub_category');
            @endphp
            
            @foreach($subCategories as $subCategory => $subCategoryDocuments)
                @if($subCategory)
                    <div class="sub-category">{{ $subCategory }}</div>
                @endif
                
                @foreach($subCategoryDocuments as $document)
                    <div class="document-item">
                        <div class="document-name">
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $document->applicability)) }}">
                                {{ $document->applicability }}
                            </span>
                            {{ $document->document_name }}
                        </div>
                        
                        @if($document->checkpoints)
                            <div class="checkpoint">
                                <strong>Checkpoints:</strong> {{ $document->checkpoints }}
                            </div>
                        @endif
                        
                        @if($document->applicability_justification)
                            <div class="justification">
                                <strong>Justification:</strong> {{ $document->applicability_justification }}
                            </div>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
        
        @if(!$loop->last)
            <div style="margin-bottom: 20px;"></div>
        @endif
    @endforeach

    <div class="footer">
        Document Checklist generated on {{ date('F j, Y \a\t g:i A') }}
    </div>
</body>
</html>