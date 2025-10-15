<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitKycRequest;
use App\Http\Requests\Api\UploadDocumentRequest;
use App\Models\KycRequest;
use App\Models\AuditLog;
use App\Services\KycService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    protected $kycService;

    public function __construct(KycService $kycService)
    {
        $this->kycService = $kycService;
    }

    /**
     * Get KYC status
     */
    public function getStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $kycRequest = $user->kycRequests()->latest()->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'kyc_status' => $user->kyc_status,
                    'kyc_request' => $kycRequest ? [
                        'id' => $kycRequest->id,
                        'status' => $kycRequest->status,
                        'document_type' => $kycRequest->document_type,
                        'submitted_at' => $kycRequest->created_at,
                        'reviewed_at' => $kycRequest->reviewed_at,
                        'review_notes' => $kycRequest->review_notes,
                    ] : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KYC status.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Submit KYC request
     */
    public function submitKyc(SubmitKycRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user already has a pending KYC request
            $existingRequest = $user->kycRequests()
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending KYC request.',
                ], 400);
            }

            // Create KYC request
            $kycRequest = $this->kycService->createKycRequest($user, [
                'document_type' => $request->document_type,
                'document_front' => $request->document_front,
                'document_back' => $request->document_back,
                'selfie' => $request->selfie,
            ]);

            // Log the KYC submission
            AuditLog::createLog($user, 'kyc_submitted', $kycRequest, [
                'document_type' => $request->document_type,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'KYC request submitted successfully.',
                'data' => [
                    'kyc_request' => [
                        'id' => $kycRequest->id,
                        'status' => $kycRequest->status,
                        'document_type' => $kycRequest->document_type,
                        'submitted_at' => $kycRequest->created_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit KYC request.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get KYC documents
     */
    public function getDocuments(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $kycRequest = $user->kycRequests()->latest()->first();

            if (!$kycRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'No KYC request found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'documents' => [
                        'document_front' => $kycRequest->getDocumentUrl('front'),
                        'document_back' => $kycRequest->getDocumentUrl('back'),
                        'selfie' => $kycRequest->getSelfieUrl(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KYC documents.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Upload KYC document
     */
    public function uploadDocument(UploadDocumentRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $file = $request->file('document');
            $type = $request->input('type'); // front, back, selfie

            // Validate file
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file.',
                ], 400);
            }

            // Store file
            $path = $file->store('kyc_documents/' . $user->id, 'local');
            
            // Update user's KYC request or create new one
            $kycRequest = $user->kycRequests()->latest()->first();
            if (!$kycRequest) {
                $kycRequest = $user->kycRequests()->create([
                    'status' => 'pending',
                    'document_type' => $request->input('document_type', 'passport'),
                ]);
            }

            // Update the appropriate field
            $field = 'document_' . $type;
            if ($type === 'selfie') {
                $field = 'selfie';
            }

            $kycRequest->update([$field => $path]);

            // Log the document upload
            AuditLog::createLog($user, 'kyc_document_uploaded', $kycRequest, [
                'type' => $type,
                'file_path' => $path,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'data' => [
                    'file_path' => $path,
                    'url' => asset('storage/' . $path),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete KYC document
     */
    public function deleteDocument(Request $request, int $documentId): JsonResponse
    {
        try {
            $user = $request->user();
            $kycRequest = $user->kycRequests()->findOrFail($documentId);

            // Delete files from storage
            if ($kycRequest->document_front) {
                Storage::delete($kycRequest->document_front);
            }
            if ($kycRequest->document_back) {
                Storage::delete($kycRequest->document_back);
            }
            if ($kycRequest->selfie) {
                Storage::delete($kycRequest->selfie);
            }

            // Delete the KYC request
            $kycRequest->delete();

            // Log the document deletion
            AuditLog::createLog($user, 'kyc_documents_deleted', $kycRequest, [
                'kyc_request_id' => $documentId,
            ], $request->ip(), $request->userAgent());

            return response()->json([
                'success' => true,
                'message' => 'KYC documents deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete KYC documents.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}