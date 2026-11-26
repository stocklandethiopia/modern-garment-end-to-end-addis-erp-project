<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentIntegrationService
{
    public function registerDocument(string $tenantId, string $actorId, array $data): object
    {
        if (! preg_match('/^[0-9a-f]{64}$/i', $data['sha256'])) {
            throw ValidationException::withMessages(['sha256' => 'A valid SHA-256 hash is required.']);
        }

        $id = (string) Str::uuid();
        DB::table('document_assets')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'document_code' => $data['document_code'],
            'document_type' => $data['document_type'],
            'original_filename' => $data['original_filename'],
            'media_type' => strtolower($data['media_type']),
            'byte_size' => $data['byte_size'],
            'sha256' => strtolower($data['sha256']),
            'object_storage_reference' => $data['object_storage_reference'],
            'source_channel' => $data['source_channel'],
            'source_record_type' => $data['source_record_type'] ?? null,
            'source_record_id' => $data['source_record_id'] ?? null,
            'classification_status' => 'PENDING',
            'access_classification' => $data['access_classification'] ?? 'BUSINESS',
            'uploaded_by' => $actorId,
            'uploaded_at' => now(),
            'retention_until' => $data['retention_until'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('document_assets')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function beginExtraction(string $tenantId, string $actorId, string $documentId, array $data): object
    {
        $document = DB::table('document_assets')->where('tenant_id', $tenantId)->where('id', $documentId)->first();
        if ($document === null || $document->classification_status === 'REJECTED') {
            throw ValidationException::withMessages(['document_id' => 'The document is not available for extraction.']);
        }

        $id = (string) Str::uuid();
        DB::table('document_extraction_runs')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'document_asset_id' => $documentId,
            'extraction_profile' => $data['extraction_profile'],
            'extractor_provider' => $data['extractor_provider'],
            'extractor_model' => $data['extractor_model'] ?? null,
            'extractor_version' => $data['extractor_version'] ?? null,
            'status' => 'QUEUED',
            'extracted_values' => json_encode([], JSON_THROW_ON_ERROR),
            'field_confidence' => json_encode([], JSON_THROW_ON_ERROR),
            'source_locations' => json_encode([], JSON_THROW_ON_ERROR),
            'validation_issues' => json_encode([], JSON_THROW_ON_ERROR),
            'proposed_domain' => $data['proposed_domain'] ?? null,
            'proposed_record_type' => $data['proposed_record_type'] ?? null,
            'created_by' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function stageExtractionResult(string $tenantId, string $runId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $runId, $data): object {
            $run = DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $runId)->lockForUpdate()->first();
            if ($run === null || ! in_array($run->status, ['QUEUED','PROCESSING'], true)) {
                throw ValidationException::withMessages(['run' => 'Only a queued or processing extraction can receive extracted values.']);
            }
            $issues = $data['validation_issues'] ?? [];
            DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $runId)->update([
                'status' => count($issues) > 0 ? 'REVIEW_REQUIRED' : 'EXTRACTED',
                'extracted_values' => json_encode($data['extracted_values'], JSON_THROW_ON_ERROR),
                'field_confidence' => json_encode($data['field_confidence'] ?? [], JSON_THROW_ON_ERROR),
                'source_locations' => json_encode($data['source_locations'] ?? [], JSON_THROW_ON_ERROR),
                'validation_issues' => json_encode($issues, JSON_THROW_ON_ERROR),
                'extraction_started_at' => $data['started_at'] ?? now(),
                'extraction_completed_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $runId)->first();
        }, 3);
    }

    public function reviewExtraction(string $tenantId, string $reviewerId, string $runId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $reviewerId, $runId, $data): object {
            $run = DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $runId)->lockForUpdate()->first();
            if ($run === null || ! in_array($run->status, ['EXTRACTED','REVIEW_REQUIRED'], true)) {
                throw ValidationException::withMessages(['run' => 'Only an extracted document can enter human review.']);
            }
            if (! in_array($data['decision'], ['APPROVE_FOR_IMPORT','REJECT'], true)) {
                throw ValidationException::withMessages(['decision' => 'Unsupported document review decision.']);
            }
            if (trim((string) ($data['reason'] ?? '')) === '') {
                throw ValidationException::withMessages(['reason' => 'A reviewer reason is required.']);
            }
            $approved = $data['decision'] === 'APPROVE_FOR_IMPORT';
            DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $runId)->update([
                'status' => $approved ? 'APPROVED_FOR_IMPORT' : 'REJECTED',
                'approved_values' => $approved ? json_encode($data['approved_values'] ?? json_decode($run->extracted_values, true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR) : null,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'review_reason' => $data['reason'],
                'updated_at' => now(),
            ]);

            return DB::table('document_extraction_runs')->where('tenant_id', $tenantId)->where('id', $runId)->first();
        }, 3);
    }

    public function enqueueDomainEvent(string $tenantId, array $data): object
    {
        if (! preg_match('/^[0-9a-f]{64}$/i', $data['payload_sha256'])) {
            throw ValidationException::withMessages(['payload_sha256' => 'A valid payload SHA-256 hash is required.']);
        }

        $existing = DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('event_key', $data['event_key'])->first();
        if ($existing !== null) {
            return $existing;
        }

        $id = (string) Str::uuid();
        DB::table('integration_outbox_events')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'endpoint_id' => $data['endpoint_id'] ?? null,
            'event_key' => $data['event_key'],
            'event_type' => $data['event_type'],
            'aggregate_type' => $data['aggregate_type'],
            'aggregate_id' => $data['aggregate_id'],
            'payload' => json_encode($data['payload'], JSON_THROW_ON_ERROR),
            'payload_sha256' => strtolower($data['payload_sha256']),
            'status' => 'PENDING',
            'attempt_count' => 0,
            'available_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('integration_outbox_events')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function recordScanEvent(string $tenantId, ?string $actorId, array $data): object
    {
        $existing = DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('event_key', $data['event_key'])->first();
        if ($existing !== null) {
            return $existing;
        }

        $id = (string) Str::uuid();
        DB::table('device_scan_events')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'event_key' => $data['event_key'],
            'device_reference' => $data['device_reference'],
            'device_type' => $data['device_type'],
            'symbology' => $data['symbology'] ?? null,
            'scanned_value' => $data['scanned_value'],
            'event_context_type' => $data['event_context_type'] ?? null,
            'event_context_id' => $data['event_context_id'] ?? null,
            'device_timestamp' => $data['device_timestamp'] ?? null,
            'received_at' => now(),
            'actor_user_id' => $actorId,
            'resolution_status' => 'PENDING',
            'raw_metadata' => json_encode($data['raw_metadata'] ?? [], JSON_THROW_ON_ERROR),
        ]);

        return DB::table('device_scan_events')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }
}
