<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequiredDocument;
use Illuminate\Support\Facades\Log;
use App\Exports\DocumentChecklistExport;
use Maatwebsite\Excel\Facades\Excel;

class DocumentChecklistController extends Controller
{
    public function canvasData(Request $request)
    {
        try {
            $entityType = $request->get('entity_type', 'company');
            
            $documents = RequiredDocument::forEntityType($entityType)
                ->orderBy('sort_order')
                ->orderBy('category')
                ->orderBy('sub_category')
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

    public function downloadExcel(Request $request)
    {
        try {
            $entityType = $request->get('entity_type', 'company');
            $entityTypeName = RequiredDocument::ENTITY_TYPES[$entityType] ?? $entityType;
            
            $filename = "document_checklist_{$entityType}_" . date('Y-m-d') . ".xlsx";

            return Excel::download(new DocumentChecklistExport($entityType), $filename);

        } catch (\Exception $e) {
            Log::error('Error downloading document checklist: ' . $e->getMessage());
            return back()->with('error', 'Error downloading document checklist');
        }
    }

    public function downloadPDF(Request $request)
    {
        try {
            $entityType = $request->get('entity_type', 'company');
            $entityTypeName = RequiredDocument::ENTITY_TYPES[$entityType] ?? $entityType;
            
            $documents = RequiredDocument::forEntityType($entityType)
                ->orderBy('sort_order')
                ->orderBy('category')
                ->orderBy('sub_category')
                ->get()
                ->groupBy('category');

            $pdf = \PDF::loadView('document-checklist.pdf', compact('documents', 'entityTypeName', 'entityType'));
            
            $filename = "document_checklist_{$entityType}_" . date('Y-m-d') . ".pdf";
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return back()->with('error', 'Error generating PDF');
        }
    }

    public function saveChecklist(Request $request)
    {
        try {
            $entityType = $request->get('entity_type', 'company');
            $documentIds = $request->get('document_ids', []);
            $status = $request->get('status', 'saved');

            // Here you can save to a user_checklists table
            // Example structure for user_checklists table:
            // - id
            // - user_id
            // - entity_type
            // - document_ids (JSON)
            // - status
            // - created_at
            // - updated_at

            Log::info("Checklist saved for entity: {$entityType}, documents: " . count($documentIds));

            return response()->json([
                'success' => true,
                'message' => 'Checklist saved successfully',
                'saved_count' => count($documentIds)
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving checklist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving checklist'
            ], 500);
        }
    }
}