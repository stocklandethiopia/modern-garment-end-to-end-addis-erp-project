<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateDocumentStorageConfigTest extends TestCase
{
    public function test_private_document_storage_defaults_to_non_served_local_disk(): void
    {
        self::assertSame('local', config('filesystems.private_documents_disk'));
        self::assertSame(storage_path('app/private'), config('filesystems.disks.local.root'));
        self::assertFalse(config('filesystems.disks.local.serve'));

        Storage::fake('local');
        $path = 'tenants/demo/documents/private-probe.txt';

        Storage::disk('local')->put($path, 'private storage test');

        self::assertTrue(Storage::disk('local')->exists($path));
    }
}
