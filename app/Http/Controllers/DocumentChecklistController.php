<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequiredDocument;
use Illuminate\Support\Facades\Log;

class DocumentChecklistController extends Controller
{
    public function canvasData(Request $request)
    {
        try {
            $entityType = $request->get('entity_type', 'company');
            
            // Simplified query - no applicability filter
            $documents = RequiredDocument::forEntityType($entityType)
                ->orderBy('sort_order')
                ->orderBy('category')
                ->get()
                ->groupBy('category');
                
            $entityTypes = RequiredDocument::ENTITY_TYPES;

            $html = view('document-checklist.canvas-content', compact('documents', 'entityTypes', 'entityType'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading document checklist canvas data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading document checklist'
            ], 500);
        }
    }
}