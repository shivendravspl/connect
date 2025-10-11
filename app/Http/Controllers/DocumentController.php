<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function processPANCard(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('PAN card processing request received', ['files' => $request->hasFile('pan_file')]);

        $request->validate([
            'pan_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $filePath = null;

        try {
            $file = $request->file('pan_file');
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "pandoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/pan/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];

            Log::info('File uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            $client = new Client();
            $extractResponse = $client->post('https://api-gf4tdduqha-uc.a.run.app/api/v1/extract-pan-card', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ['fileUrl' => $fileUrl],
            ]);

            $extractData = json_decode($extractResponse->getBody(), true);
            Log::info('PAN extraction response', ['data' => $extractData]);

            if (!$extractData['success'] || empty($extractData['data']['panNumber'])) {
                throw new \Exception('PAN number extraction failed');
            }

            $panNumber = $extractData['data']['panNumber'];

            Log::info('Sending PAN verification request', ['pan_number' => $panNumber]);

            $verifyResponse = $client->post('https://api.rpacpc.com/services/get-pan-nsdl-details', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'token' => 'HZqJwTTU+6SnoILGiwfD2h6Lgpp977mCfFJ4+XrnVvUDKENPJ0WjgRGO0uv9NODrf7KjCl6d34LQJOvn8w/aih79BZHUU6zKzfcoQDLBHkC8SoCaffiBcFvjagMjwnDrQmL6qb6+dmWi8rqFBWV3Sy/utyhxsFxC6N8FdIkvnBjKKlugKVCSssdECP07PB3sCJfU+I6pCWm8uF+4cCROXSXZvNRqaOqap9B/bSIUzSQ89j+Z8CdAhjF6MoKleyj5EsgLvfkybuovyiUscldmbgL6xKDnOwGOB5a3cZgk+/An0SZ92UMRAubEidLDw9lqf+8mmjVdIsfVzu9M5rTYh6ztfDksYcvYQ3kMJpvpUwcinGFCyRg+nW/bJPSv8TGFVs9E+tEgIzr92xryXc2WeEHAinwzVol0gkwfYMvcVJah0qn6gfKXkW/53zCDx4Yd0UWIipAHPPWyKKX2O9RI9g==',
                    'secretkey' => 'f0e07252-46b4-4d31-9f76-54f92d3b7d60',
                ],
                'json' => ['pan_number' => $panNumber],
            ]);

            $verifyData = json_decode($verifyResponse->getBody(), true);
            Log::info('PAN verification response', ['data' => $verifyData]);

            if ($verifyData['status'] !== 'SUCCESS' || empty($verifyData['data']['is_valid']) || !$verifyData['data']['is_valid']) {
                throw new \Exception('PAN verification failed');
            }

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'panNumber' => $panNumber,
                    'verification' => $verifyData['data'],
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'PAN extracted and verified successfully',
            ]);
        } catch (\Exception $e) {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }

            Log::error('PAN processing error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process PAN card: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function processBankDocument(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('Bank document processing request received', ['files' => $request->hasFile('bank_file')]);

        $request->validate([
            'bank_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $filePath = null;

        try {
            $file = $request->file('bank_file');
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "bankdoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/bank/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];

            Log::info('File uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            $client = new Client();
            $extractResponse = $client->post('https://api-gf4tdduqha-uc.a.run.app/api/v1/extract-cheque-details', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ['fileUrl' => $fileUrl],
            ]);

            $extractData = json_decode($extractResponse->getBody(), true);
            Log::info('Bank extraction response', ['data' => $extractData]);

            if (!$extractData['success'] || empty($extractData['data']['accountNumber'])) {
                throw new \Exception('Bank details extraction failed');
            }

            $accountNumber = $extractData['data']['accountNumber'];
            $ifscCode = $extractData['data']['ifscCode'];

            Log::info('Sending bank verification request', [
                'account_number' => $accountNumber,
                'ifsc_code' => $ifscCode,
            ]);

            $verifyResponse = $client->post('https://api.rpacpc.com/services/account-verification-pl', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'token' => 'HZqJwTTU+6SnoILGiwfD2h6Lgpp977mCfFJ4+XrnVvUDKENPJ0WjgRGO0uv9NODrf7KjCl6d34LQJOvn8w/aih79BZHUU6zKzfcoQDLBHkC8SoCaffiBcFvjagMjwnDrQmL6qb6+dmWi8rqFBWV3Sy/utyhxsFxC6N8FdIkvnBjKKlugKVCSssdECP07PB3sCJfU+I6pCWm8uF+4cCROXSXZvNRqaOqap9B/bSIUzSQ89j+Z8CdAhjF6MoKleyj5EsgLvfkybuovyiUscldmbgL6xKDnOwGOB5a3cZgk+/An0SZ92UMRAubEidLDw9lqf+8mmjVdIsfVzu9M5rTYh6ztfDksYcvYQ3kMJpvpUwcinGFCyRg+nW/bJPSv8TGFVs9E+tEgIzr92xryXc2WeEHAinwzVol0gkwfYMvcVJah0qn6gfKXkW/53zCDx4Yd0UWIipAHPPWyKKX2O9RI9g==',
                    'secretkey' => 'f0e07252-46b4-4d31-9f76-54f92d3b7d60',
                ],
                'json' => [
                    'acc_number' => $accountNumber,
                    'ifsc_number' => $ifscCode,
                ],
            ]);

            $data = json_decode($verifyResponse->getBody(), true);
            Log::info('Bank verification response', ['data' => $data]);

            if ($data['status'] !== 'SUCCESS' || empty($data['data']['verification_status']) || $data['data']['verification_status'] !== 'VERIFIED') {
                throw new \Exception('Bank verification failed');
            }

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'accountNumber' => $accountNumber,
                    'verification' => $data['data'],
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'Bank extracted and verified successfully',
            ]);
        } catch (\Exception $e) {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }

            Log::error('Bank processing error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process bank details: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function processSeedLicense(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('Seed license processing request received', ['files' => $request->hasFile('seed_license_file')]);

        $request->validate([
            'seed_license_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'existing_seed_license_file' => 'nullable|string',
        ]);

        $filePath = null;

        try {
            $file = $request->file('seed_license_file');
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            // Delete existing file if provided
            if ($request->has('existing_seed_license_file') && $request->input('existing_seed_license_file')) {
                $existingFilePath = "Connect/Distributor/seed_license/" . $request->input('existing_seed_license_file');
                if (Storage::disk('s3')->exists($existingFilePath)) {
                    Storage::disk('s3')->delete($existingFilePath);
                    Log::info('Deleted existing seed license file', ['filePath' => $existingFilePath]);
                }
            }

            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "seeddoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/seed_license/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];

            Log::info('File uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'Seed license uploaded successfully',
            ]);
        } catch (\Exception $e) {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }

            Log::error('Seed license processing error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process seed license: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processDocument(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('Document upload request received', [
            'doc_type' => $request->input('doc_type'),
            'hasFile' => $request->hasFile($request->input('doc_type'))
        ]);

        $docType = $request->input('doc_type');
        $validDocTypes = [
            'entity_proof',
            'ownership_info',
            'bank_statement',
            'itr_acknowledgement',
            'balance_sheet',
            'partner_aadhar',
            'agreement_copy',
            'security_cheques',
            'security_deposit'
        ];

        if (!in_array($docType, $validDocTypes)) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Invalid document type',
            ], 400);
        }

        $request->validate([
            $docType => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        try {
            $file = $request->file($docType);
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "{$docType}_{$timestamp}.{$extension}";
            $s3BasePath = "Connect/Distributor/{$docType}/";
            $filePath = $s3BasePath . $filename;

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];

            Log::info("{$docType} uploaded to S3", ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                    'url' => $fileUrl,
                ],
                'message' => "{$docType} uploaded successfully",
            ]);
        } catch (\Exception $e) {
            Log::error("{$docType} upload error", ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => "Unable to upload {$docType}: " . $e->getMessage(),
            ], 500);
        }
    }

    // Existing method (unchanged)
    // public function processEntityProof(Request $request, \App\Services\S3Service $s3Service)
    // {
    //     Log::info('Entity proof upload request received', ['hasFile' => $request->hasFile('entity_proof_file')]);

    //     $request->validate([
    //         'entity_proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
    //     ]);

    //     try {
    //         $file = $request->file('entity_proof_file');
    //         if (!$file->isValid()) {
    //             throw new \Exception('Invalid file uploaded');
    //         }

    //         $extension = $file->getClientOriginalExtension();
    //         $timestamp = now()->timestamp;
    //         $filename = "entityproof_{$timestamp}.{$extension}";
    //         $filePath = "Connect/Distributor/entity_proof/{$filename}";

    //         $upload = $s3Service->uploadFile($file, $filePath, 'public');
    //         if (!$upload['success']) {
    //             throw new \Exception('S3 Upload failed: ' . $upload['error']);
    //         }

    //         $fileUrl = $upload['url'];

    //         Log::info('Entity proof uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

    //         return response()->json([
    //             'status' => 'SUCCESS',
    //             'data' => [
    //                 'filename' => $filename,
    //                 'displayName' => $file->getClientOriginalName(),
    //                 'url' => $fileUrl,
    //             ],
    //             'message' => 'Entity proof uploaded successfully',
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Entity proof upload error', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'status' => 'FAILURE',
    //             'message' => 'Unable to upload entity proof: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function processGSTDocument(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('GST document processing request received', ['files' => $request->hasFile('gst_file')]);

        $request->validate([
            'gst_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'existing_gst_file' => 'nullable|string',
        ]);

        $filePath = null;

        try {
            $file = $request->file('gst_file');
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            // Delete existing file if provided
            if ($request->has('existing_gst_file') && $request->input('existing_gst_file')) {
                $existingFilePath = "Connect/Distributor/gst/" . $request->input('existing_gst_file');
                if (Storage::disk('s3')->exists($existingFilePath)) {
                    Storage::disk('s3')->delete($existingFilePath);
                    Log::info('Deleted existing GST file', ['filePath' => $existingFilePath]);
                }
            }

            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "gstdoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/gst/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];

            Log::info('File uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'GST document uploaded successfully',
            ]);
        } catch (\Exception $e) {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }

            Log::error('GST processing error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process GST document: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processLetterDocument(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('Letter document processing request received', ['files' => $request->hasFile('auth_person_letter')]);

        $request->validate([
            'auth_person_letter' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'existing_auth_person_letter' => 'nullable|string',
        ]);

        $filePath = null;

        try {
            $file = $request->file('auth_person_letter');
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            // Delete existing file if provided
            if ($request->has('existing_auth_person_letter') && $request->input('existing_auth_person_letter')) {
                $existingFilePath = 'Connect/Distributor/authorized_persons/' . $request->input('existing_auth_person_letter');
                if (Storage::disk('s3')->exists($existingFilePath)) {
                    Storage::disk('s3')->delete($existingFilePath);
                    Log::info('Deleted existing Letter file', ['filePath' => $existingFilePath]);
                }
            }

            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "letterdoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/authorized_persons/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];

            Log::info('Letter of Authorization uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'Letter of Authorization uploaded successfully',
            ], 200);
        } catch (\Exception $e) {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
                Log::info('Cleaned up failed Letter upload', ['filePath' => $filePath]);
            }

            Log::error('Letter processing error', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process Letter of Authorization: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processAadharDocument(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('Aadhar document processing request received', [
            'files' => $request->hasFile('auth_person_aadhar'),
            'existing_file' => $request->input('existing_auth_person_aadhar'),
        ]);

        try {
            // Validate the request
            $request->validate([
                'auth_person_aadhar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'existing_auth_person_aadhar' => 'nullable|string', // Keep as string for single value
            ]);

            $file = $request->file('auth_person_aadhar');
            if (!$file->isValid()) {
                throw new \Exception('Invalid file uploaded');
            }

            // Handle existing file deletion
            $existingFile = $request->input('existing_auth_person_aadhar');
            if (is_array($existingFile)) {
                // If an array is received, take the first element or handle appropriately
                $existingFile = reset($existingFile) ?: null;
            }

            if ($existingFile) {
                $existingFilePath = 'Connect/Distributor/authorized_persons/' . $existingFile;
                if (Storage::disk('s3')->exists($existingFilePath)) {
                    Storage::disk('s3')->delete($existingFilePath);
                    Log::info('Deleted existing Aadhar file', ['filePath' => $existingFilePath]);
                }
            }

            // Upload new file
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "aadhdoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/authorized_persons/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) {
                throw new \Exception('S3 Upload failed: ' . $upload['error']);
            }

            $fileUrl = $upload['url'];
            Log::info('Aadhar uploaded to S3', ['filePath' => $filePath, 'fileUrl' => $fileUrl]);

            // Call Aadhar extraction API
            $client = new Client();
            $extractResponse = $client->post('https://api-gf4tdduqha-uc.a.run.app/api/v1/extract-aadhaar-number', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => ['fileUrl' => $fileUrl],
            ]);

            $extractData = json_decode($extractResponse->getBody(), true);
            Log::info('Aadhar extraction response', ['data' => $extractData]);

            if (!$extractData['success'] || empty($extractData['data']['aadhaarNumber'])) {
                // Clean up uploaded file on failure
                Storage::disk('s3')->delete($filePath);
                throw new \Exception('Aadhar number extraction failed: ' . ($extractData['message'] ?? 'No Aadhaar number found'));
            }

            $aadharNumber = $extractData['data']['aadhaarNumber'];

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'aadharNumber' => $aadharNumber,
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'Aadhar extracted successfully',
            ]);
        } catch (\Exception $e) {
            // Clean up if file was uploaded
            if (isset($filePath) && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
                Log::info('Cleaned up failed Aadhar upload', ['filePath' => $filePath]);
            }

            Log::error('Aadhar processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process Aadhar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processPartnerAadhar(Request $request, \App\Services\S3Service $s3Service)
    {
        Log::info('Partner Aadhar processing request received', [
            'files' => $request->hasFile('partner_aadhar'),
            'existing_file' => $request->input('existing_partner_aadhar_file'),
        ]);

        try {
            $request->validate([
                'partner_aadhar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'existing_partner_aadhar_file' => 'nullable|string',
            ]);

            $file = $request->file('partner_aadhar');
            if (!$file->isValid()) throw new \Exception('Invalid file uploaded');

            $existingFile = $request->input('existing_partner_aadhar_file');
            if (is_array($existingFile)) $existingFile = reset($existingFile);

            if ($existingFile) {
                $existingFilePath = 'Connect/Distributor/partner_aadhar/' . $existingFile;
                if (Storage::disk('s3')->exists($existingFilePath)) {
                    Storage::disk('s3')->delete($existingFilePath);
                    Log::info('Deleted existing Partner Aadhar file', ['filePath' => $existingFilePath]);
                }
            }

            // Upload new file
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->timestamp;
            $filename = "partner_aadhdoc_{$timestamp}.{$extension}";
            $filePath = "Connect/Distributor/partner_aadhar/{$filename}";

            $upload = $s3Service->uploadFile($file, $filePath, 'public');
            if (!$upload['success']) throw new \Exception('S3 Upload failed: ' . $upload['error']);

            Log::info('Partner Aadhar uploaded to S3', ['filePath' => $filePath]);

            return response()->json([
                'status' => 'SUCCESS',
                'data' => [
                    'filename' => $filename,
                    'displayName' => $file->getClientOriginalName(),
                ],
                'message' => 'Partner Aadhar uploaded successfully',
            ]);
        } catch (\Exception $e) {
            if (isset($filePath) && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
                Log::info('Cleaned up failed Partner Aadhar upload', ['filePath' => $filePath]);
            }

            Log::error('Partner Aadhar processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to process Partner Aadhar: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function fetchDetails(Request $request)
    {
        $request->validate([
            'account_number' => 'required',
            'ifsc_code' => 'required'
        ]);

        try {
            $client = new Client();
            $response = $client->post('https://api.rpacpc.com/services/account-verification-pl', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'token' => 'HZqJwTTU+6SnoILGiwfD2h6Lgpp977mCfFJ4+XrnVvUDKENPJ0WjgRGO0uv9NODrf7KjCl6d34LQJOvn8w/aih79BZHUU6zKzfcoQDLBHkC8SoCaffiBcFvjagMjwnDrQmL6qb6+dmWi8rqFBWV3Sy/utyhxsFxC6N8FdIkvnBjKKlugKVCSssdECP07PB3sCJfU+I6pCWm8uF+4cCROXSXZvNRqaOqap9B/bSIUzSQ89j+Z8CdAhjF6MoKleyj5EsgLvfkybuovyiUscldmbgL6xKDnOwGOB5a3cZgk+/An0SZ92UMRAubEidLDw9lqf+8mmjVdIsfVzu9M5rTYh6ztfDksYcvYQ3kMJpvpUwcinGFCyRg+nW/bJPSv8TGFVs9E+tEgIzr92xryXc2WeEHAinwzVol0gkwfYMvcVJah0qn6gfKXkW/53zCDx4Yd0UWIipAHPPWyKKX2O9RI9g==',       // store in .env
                    'secretkey' => 'f0e07252-46b4-4d31-9f76-54f92d3b7d60',  // store in .env
                ],
                'json' => [
                    'acc_number' => $request->account_number,
                    'ifsc_number' => $request->ifsc_code
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'FAILURE',
                'message' => 'Unable to fetch bank details. ' . $e->getMessage()
            ], 500);
        }
    }
}
