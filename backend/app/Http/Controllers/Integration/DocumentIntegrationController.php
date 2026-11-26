<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Services\Integration\DocumentIntegrationService;
use App\Services\Tenant\TenantContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentIntegrationController extends Controller
{
    public function __construct(
        private readonly DocumentIntegrationService $documents,
        private readonly TenantContextResolver $tenant
    ) {
    }


    public function uploadFile(Request $request): JsonResponse
    {
        Gate::authorize('documents.register');
        $data = $request->validate([
            'file' => ['required','file','max:102400'],
            'document_code' => ['required','string','max:100'],
            'document_type' => ['required','string','max:60'],
            'source_record_type' => ['nullable','string','max:80'],
            'source_record_id' => ['nullable','uuid'],
            'access_classification' => ['nullable','in:PUBLIC,BUSINESS,CONFIDENTIAL,RESTRICTED'],
            'retention_until' => ['nullable','date'],
        ]);

        return response()->json([
            'data' => $this->documents->storeUploadedFile(
                $this->tenant->requireTenantId(),
                $this->tenant->requireActorId(),
                $data['file'],
                $data
            ),
        ], 201);
    }

    public function registerDocument(Request $request): JsonResponse
    {
        Gate::authorize('documents.register');
        $data = $request->validate([
            'document_code' => ['required','string','max:100'],
            'document_type' => ['required','string','max:60'],
            'original_filename' => ['required','string','max:255'],
            'media_type' => ['required','string','max:160'],
            'byte_size' => ['required','integer','min:1','max:104857600'],
            'sha256' => ['required','string','size:64'],
            'object_storage_reference' => ['required','string','max:1000'],
            'source_channel' => ['required','in:UPLOAD,CAMERA,EMAIL,INTEGRATION,SCANNER,GENERATED'],
            'source_record_type' => ['nullable','string','max:80'],
            'source_record_id' => ['nullable','uuid'],
            'access_classification' => ['nullable','in:PUBLIC,BUSINESS,CONFIDENTIAL,RESTRICTED'],
            'retention_until' => ['nullable','date'],
        ]);

        return response()->json(['data' => $this->documents->registerDocument($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }

    public function beginExtraction(Request $request, string $documentId): JsonResponse
    {
        Gate::authorize('documents.extract');
        $data = $request->validate([
            'extraction_profile' => ['required','string','max:100'],
            'extractor_provider' => ['required','string','max:80'],
            'extractor_model' => ['nullable','string','max:120'],
            'extractor_version' => ['nullable','string','max:80'],
            'proposed_domain' => ['nullable','string','max:80'],
            'proposed_record_type' => ['nullable','string','max:100'],
        ]);

        return response()->json(['data' => $this->documents->beginExtraction($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $documentId, $data)], 201);
    }

    public function stageExtractionResult(Request $request, string $runId): JsonResponse
    {
        Gate::authorize('documents.stage-extraction');
        $data = $request->validate([
            'extracted_values' => ['required','array'],
            'field_confidence' => ['nullable','array'],
            'source_locations' => ['nullable','array'],
            'validation_issues' => ['nullable','array'],
            'started_at' => ['nullable','date'],
        ]);

        return response()->json(['data' => $this->documents->stageExtractionResult($this->tenant->requireTenantId(), $runId, $data)]);
    }

    public function reviewExtraction(Request $request, string $runId): JsonResponse
    {
        Gate::authorize('documents.approve-import');
        $data = $request->validate([
            'decision' => ['required','in:APPROVE_FOR_IMPORT,REJECT'],
            'approved_values' => ['nullable','array'],
            'reason' => ['required','string','max:2000'],
        ]);

        return response()->json(['data' => $this->documents->reviewExtraction($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $runId, $data)]);
    }

    public function recordScan(Request $request): JsonResponse
    {
        Gate::authorize('devices.record-scan');
        $data = $request->validate([
            'event_key' => ['required','string','max:180'],
            'device_reference' => ['required','string','max:160'],
            'device_type' => ['required','in:CAMERA,BARCODE_SCANNER,QR_SCANNER,RFID_READER,MACHINE_TERMINAL,MOBILE_APP,OTHER'],
            'symbology' => ['nullable','string','max:32'],
            'scanned_value' => ['required','string','max:500'],
            'event_context_type' => ['nullable','string','max:100'],
            'event_context_id' => ['nullable','uuid'],
            'device_timestamp' => ['nullable','date'],
            'raw_metadata' => ['nullable','array'],
        ]);

        return response()->json(['data' => $this->documents->recordScanEvent($this->tenant->requireTenantId(), $this->tenant->requireActorId(), $data)], 201);
    }
}
