<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\DocumentVerification;
use App\Models\DistributorAgreement;
use App\Models\PhysicalDocument;
use App\Models\DistributorMaster;
use App\Models\Employee;
use App\Mail\ApplicationNotification;
use App\Mail\DistributorCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PDF;

class MISProcessingController extends Controller
{
    public function showDocumentVerification(Onboarding $application)
    {
        $this->authorizeMisAction();
        $application->load(['documentVerifications', 'entityDetails', 'territoryDetail']);
        $documentTypes = ['business_entity_proofs', 'ownership_confirmation', 'all_required_documents'];

        return view('mis.verify-documents', compact('application', 'documentTypes'));
    }

    public function verifyDocuments(Request $request, Onboarding $application)
    {
        $this->authorizeMisAction();

        // Validate request
        $validated = $request->validate([
            'checkpoints' => 'required|array',
            'checkpoints.*.status' => 'required|in:verified,rejected',
            'checkpoints.*.remarks' => 'nullable|string|max:255',
            'additional_requirements' => 'nullable|array',
            'additional_requirements.*.name' => 'nullable|string|max:255',
            'additional_requirements.*.remark' => 'nullable|string|max:255',
        ]);

        // Process checkpoint verifications
        foreach ($validated['checkpoints'] as $checkpoint => $data) {
            DocumentVerification::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'document_type' => $checkpoint,
                ],
                [
                    'verified_by' => Auth::user()->emp_id,
                    'status' => $data['status'],
                    'remarks' => $data['remarks'],
                    'verified_at' => now(),
                ]
            );
        }

        // Process additional requirements
        if (!empty($validated['additional_requirements'])) {
            DocumentVerification::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'document_type' => 'additional_requirements',
                ],
                [
                    'verified_by' => Auth::user()->emp_id,
                    'status' => 'pending',
                    'remarks' => json_encode($validated['additional_requirements']),
                    'verified_at' => now(),
                ]
            );
        } else {
            DocumentVerification::where('application_id', $application->id)
                ->where('document_type', 'additional_requirements')
                ->delete();
        }

        // Check if all checkpoints are verified
        $allVerified = !DocumentVerification::where('application_id', $application->id)
            ->where('document_type', '!=', 'additional_requirements')
            ->where('status', '!=', 'verified')
            ->exists();

        // Update application status and prepare response
        $response = [
            'message' => 'Document verification status updated successfully.',
            'status' => $allVerified && empty($validated['additional_requirements']) ? 'document_verified' : 'mis_rejected',
        ];

        if ($response['status'] === 'document_verified') {
            $application->update([
                'status' => 'document_verified',
                'approval_level' => 'mis', // Set approval_level to 'mis'
            ]);
            $this->notifySalesTeam($application, 'All Documents Verified');
            try {
                $response['next_step'] = [
                    'action' => 'Upload Agreement',
                    'url' => route('approvals.upload-agreement', $application),
                ];
            } catch (\Exception $e) {
                $response['next_step'] = [
                    'action' => 'Return to Dashboard',
                    'url' => route('mis.dashboard'),
                ];
            }
        } else {
            $application->update([
                'status' => 'mis_rejected',
                'approval_level' => 'mis', // Set approval_level to 'mis'
            ]);
            $rejectionDetails = collect($validated['checkpoints'])
                ->filter(fn($data) => $data['status'] === 'rejected')
                ->map(fn($data, $checkpoint) => [
                    'document_type' => ucfirst(str_replace('_', ' ', $checkpoint)),
                    'remarks' => $data['remarks'] ?? 'No remarks provided',
                ])
                ->values()
                ->toArray();

            if (!empty($validated['additional_requirements'])) {
                $rejectionDetails[] = [
                    'document_type' => 'Additional Requirements',
                    'remarks' => collect($validated['additional_requirements'])
                        ->map(fn($req) => "{$req['name']}: {$req['remark']}")
                        ->implode('; '),
                ];
            }

            $this->notifySalesTeam($application, 'Documents Rejected', $rejectionDetails);
        }

        // Log the verification
        Log::info("Document verification updated for application_id: {$application->id}", [
            'checkpoints' => $validated['checkpoints'],
            'additional_requirements' => $validated['additional_requirements'] ?? [],
            'user_id' => Auth::user()->emp_id,
            'status' => $response['status'],
            'approval_level' => 'mis',
        ]);

        // Return JSON response for AJAX
        return response()->json($response, 200);
    }

    public function generateAgreement(Request $request, Onboarding $application)
    {
        $this->authorizeMisAction();
        $unverified = DocumentVerification::where('application_id', $application->id)
            ->where('status', '!=', 'verified')
            ->exists();

        if ($unverified) {
            return response()->json([
                'message' => 'Cannot generate agreement - some documents are not verified',
            ], 422);
        }

        $request->validate([
            'agreement_file' => 'required|file|mimes:pdf|max:2048',
        ]);

        $path = $request->file('agreement_file')->store('agreements', 'public');
        DistributorAgreement::create([
            'application_id' => $application->id,
            'agreement_path' => $path,
            'generated_by' => Auth::user()->emp_id,
            'generated_at' => now(),
        ]);

        $application->update([
            'status' => 'agreement_created',
            'approval_level' => 'mis', // Set approval_level to 'mis'
        ]);
        $this->notifySalesTeam($application, 'Distributor Agreement Generated');

        Log::info("Agreement generated for application_id: {$application->id}", [
            'path' => $path,
            'user_id' => Auth::user()->emp_id,
            'approval_level' => 'mis',
        ]);

        return response()->json([
            'message' => 'Agreement uploaded successfully.',
            'next_step' => [
                'action' => 'Track Physical Documents',
                'url' => route('approvals.track-documents', $application),
            ],
        ], 200);
    }

    public function showPhysicalDocumentTracking(Onboarding $application)
    {
        $this->authorizeMisAction();
        $application->load(['physicalDocuments', 'entityDetails', 'territoryDetail']);
        return view('mis.track-documents', compact('application'));
    }

    public function trackPhysicalDocuments(Request $request, Onboarding $application)
    {
        $this->authorizeMisAction();

        $request->validate([
            'agreement_received' => 'boolean',
            'agreement_received_date' => 'nullable|date|required_if:agreement_received,1',
            'agreement_verified' => 'boolean',
            'agreement_verified_date' => 'nullable|date|required_if:agreement_verified,1',
            'security_cheque_received' => 'boolean',
            'security_cheque_received_date' => 'nullable|date|required_if:security_cheque_received,1',
            'security_cheque_verified' => 'boolean',
            'security_cheque_verified_date' => 'nullable|date|required_if:security_cheque_verified,1',
            'security_deposit_received' => 'boolean',
            'security_deposit_received_date' => 'nullable|date|required_if:security_deposit_received,1',
            'security_deposit_verified' => 'boolean',
            'security_deposit_verified_date' => 'nullable|date|required_if:security_deposit_verified,1',
            'security_deposit_amount' => 'nullable|numeric|min:0|required_if:security_deposit_received,1',
        ]);

        PhysicalDocument::updateOrCreate(
            ['application_id' => $application->id],
            [
                'agreement_received' => $request->agreement_received,
                'agreement_received_date' => $request->agreement_received_date,
                'agreement_verified' => $request->agreement_verified,
                'agreement_verified_date' => $request->agreement_verified ? now() : null,
                'agreement_verified_by' => $request->agreement_verified ? Auth::user()->emp_id : null,
                'security_cheque_received' => $request->security_cheque_received,
                'security_cheque_received_date' => $request->security_cheque_received_date,
                'security_cheque_verified' => $request->security_cheque_verified,
                'security_cheque_verified_date' => $request->security_cheque_verified ? now() : null,
                'security_cheque_verified_by' => $request->security_cheque_verified ? Auth::user()->emp_id : null,
                'security_deposit_received' => $request->security_deposit_received,
                'security_deposit_received_date' => $request->security_deposit_received_date,
                'security_deposit_verified' => $request->security_deposit_verified,
                'security_deposit_verified_date' => $request->security_deposit_verified ? now() : null,
                'security_deposit_verified_by' => $request->security_deposit_verified ? Auth::user()->emp_id : null,
                'security_deposit_amount' => $request->security_deposit_amount,
            ]
        );

        $docs = PhysicalDocument::where('application_id', $application->id)->first();
        $allVerified = $docs && $docs->agreement_verified && $docs->security_cheque_verified && $docs->security_deposit_verified;

        if ($allVerified) {
            $application->update([
                'status' => 'documents_received',
                'approval_level' => 'mis',
            ]);
            $this->createDistributorMaster($application);
        } else {
            $application->update([
                'status' => 'documents_pending',
                'approval_level' => 'mis',
            ]);
        }

        Log::info("Physical documents updated for application_id: {$application->id}", [
            'data' => $request->all(),
            'user_id' => Auth::user()->emp_id,
            'status' => $allVerified ? 'documents_received' : 'documents_pending',
        ]);

        return redirect()->route('mis.dashboard')->with('success', 'Physical document status updated');
    }
    private function createDistributorMaster(Onboarding $application)
    {
        $distributor = DistributorMaster::create([
            'application_id' => $application->id,
            'territory_id' => $application->territory,
            'distributor_code' => $this->generateDistributorCode($application),
            'name' => $application->entityDetails->legal_name ?? 'Distributor ' . $application->id,
            'entity_type' => $application->entityDetails->entity_type ?? 'Unknown',
            'pan_number' => $application->entityDetails->pan_number ?? 'N/A',
            'gst_number' => $application->entityDetails->gst_number ?? 'N/A',
            'agreement_date' => now(),
            'security_cheque_amount' => $application->physicalDocuments->first()->security_deposit_amount ?? 0,
            'security_deposit_amount' => $application->physicalDocuments->first()->security_deposit_amount ?? 0,
            'status' => 'active',
            'created_by' => Auth::user()->emp_id,
        ]);

        $application->update([
            'status' => 'distributorship_created',
            'approval_level' => 'completed',
        ]);
        $this->notifyFinalApproval($application, $distributor);

        Log::info("Distributor master created for application_id: {$application->id}", [
            'distributor_id' => $distributor->id,
            'user_id' => Auth::user()->emp_id,
            'approval_level' => 'completed',
        ]);
    }

    private function generateDistributorCode(Onboarding $application)
    {
        $prefix = 'DIST';
        $namePart = strtoupper(substr($application->entityDetails->legal_name ?? 'UNK', 0, 3));
        return $prefix . $namePart . $application->id;
    }

    private function notifySalesTeam(Onboarding $application, $subject, $rejectionDetails = null)
    {
        $salesTeam = Employee::whereIn('employee_id', array_merge(
            [$application->created_by],
            $application->approvalLogs->pluck('user_id')->toArray()
        ))->get();

        foreach ($salesTeam as $member) {
            if ($member->emp_email) {
                Mail::to($member->emp_email)->queue(
                    new ApplicationNotification($application, $subject, $rejectionDetails)
                );
                Log::info("Queued notification for application_id: {$application->id}, recipient: {$member->emp_email}, subject: {$subject}");
            }
        }
    }

    private function notifyFinalApproval(Onboarding $application, DistributorMaster $distributor)
    {
        $recipients = array_unique(array_merge(
            [$application->created_by],
            $application->approvalLogs->pluck('user_id')->toArray(),
            Employee::where('emp_department', 'MIS')->pluck('id')->toArray(),
            Employee::whereIn('emp_designation', ['RBM', 'ZBM', 'GM', 'Business Head'])->pluck('id')->toArray()
        ));

        $employees = Employee::whereIn('employee_id', $recipients)->get();

        foreach ($employees as $employee) {
            if (filter_var($employee->emp_email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($employee->emp_email)
                    ->queue(new DistributorCreatedNotification(
                        application: $application,
                        distributor: $distributor
                    ));

                Log::channel('emails')->debug('Distributor notification queued', [
                    'application_id' => $application->id,
                    'distributor_id' => $distributor->id,
                    'recipient' => $employee->emp_email,
                    'employee_id' => $employee->id
                ]);
            } else {
                Log::channel('emails')->warning('Invalid email skipped', [
                    'employee_id' => $employee->id,
                    'invalid_email' => $employee->emp_email
                ]);
            }
        }
    }

    public function showAgreementUpload(Onboarding $application)
    {
        $this->authorizeMisAction();

        // Ensure the application is in the correct status
        if ($application->status !== 'document_verified') {
            return redirect()->route('mis.dashboard')->with('error', 'Cannot upload agreement: Documents not fully verified.');
        }

        $application->load(['entityDetails', 'territoryDetail']);
        return view('mis.upload-agreement', compact('application'));
    }

    private function authorizeMisAction()
    {
        $user = Auth::user();
        if (!$user->employee->isMisTeam()) {
            Log::warning("Unauthorized MIS action attempt by emp_id: {$user->emp_id}");
            abort(403, 'Unauthorized action');
        }
    }
}
