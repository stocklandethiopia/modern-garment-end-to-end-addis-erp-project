<?php

namespace Tests\Feature;

use App\Services\Integration\DocumentIntegrationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentIntegrationStorageTest extends TestCase
{
    private string $tenantId;
    private string $actorId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = (string) Str::uuid();
        $this->actorId = (string) Str::uuid();
        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        $admin->transaction(function () use ($admin, $now): void {
            $admin->table('tenants')->insert([
                'id' => $this->tenantId,
                'code' => 'DOC-'.Str::upper(Str::random(8)),
                'name' => 'Document storage test tenant',
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $admin->table('users')->insert([
                'id' => $this->actorId,
                'name' => 'Document Test Actor',
                'email' => 'document-'.Str::lower(Str::random(8)).'@example.test',
                'password' => Hash::make('document-storage-test-password'),
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        config(['filesystems.private_documents_disk' => 'local']);
        Storage::fake('local');
    }

    public function test_upload_persists_private_object_and_tenant_scoped_metadata(): void
    {
        $service = app(DocumentIntegrationService::class);
        $folder = 'tenants/'.$this->tenantId.'/documents/'.now()->format('Y/m');

        $document = DB::transaction(function () use ($service): object {
            DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$this->tenantId]);

            return $service->storeUploadedFile(
                $this->tenantId,
                $this->actorId,
                UploadedFile::fake()->create('supplier-evidence.txt', 2, 'text/plain'),
                [
                    'document_code' => 'DOC-'.Str::upper(Str::random(8)),
                    'document_type' => 'SUPPLIER_EVIDENCE',
                    'source_channel' => 'UPLOAD',
                    'access_classification' => 'CONFIDENTIAL',
                ],
            );
        });

        self::assertSame($this->tenantId, $document->tenant_id);
        self::assertSame('CONFIDENTIAL', $document->access_classification);
        self::assertStringStartsWith('local:', $document->object_storage_reference);

        $objectPath = substr($document->object_storage_reference, strlen('local:'));
        self::assertTrue(Storage::disk('local')->exists($objectPath));
        self::assertStringStartsWith($folder.'/', $objectPath);
    }

    public function test_uploaded_object_is_removed_when_metadata_registration_fails(): void
    {
        $admin = DB::connection('pgsql_test_admin');
        $now = now();
        $admin->table('document_assets')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantId,
            'document_code' => 'DOC-DUPLICATE',
            'document_type' => 'SUPPLIER_EVIDENCE',
            'original_filename' => 'existing.txt',
            'media_type' => 'text/plain',
            'byte_size' => 1,
            'sha256' => str_repeat('0', 64),
            'object_storage_reference' => 'local:seed/existing.txt',
            'source_channel' => 'UPLOAD',
            'classification_status' => 'PENDING',
            'access_classification' => 'BUSINESS',
            'uploaded_by' => $this->actorId,
            'uploaded_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $service = app(DocumentIntegrationService::class);
        $prefix = 'tenants/'.$this->tenantId.'/documents';

        try {
            DB::transaction(function () use ($service): void {
                DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$this->tenantId]);

                $service->storeUploadedFile(
                    $this->tenantId,
                    $this->actorId,
                    UploadedFile::fake()->create('duplicate-evidence.txt', 2, 'text/plain'),
                    [
                        'document_code' => 'DOC-DUPLICATE',
                        'document_type' => 'SUPPLIER_EVIDENCE',
                        'source_channel' => 'UPLOAD',
                    ],
                );
            });

            self::fail('Duplicate document metadata must be rejected.');
        } catch (QueryException) {
            // The metadata insert failed; the service must compensate the object write.
        }

        self::assertSame([], Storage::disk('local')->allFiles($prefix));
        self::assertSame(
            1,
            $admin->table('document_assets')
                ->where('tenant_id', $this->tenantId)
                ->where('document_code', 'DOC-DUPLICATE')
                ->count(),
        );
    }
}
