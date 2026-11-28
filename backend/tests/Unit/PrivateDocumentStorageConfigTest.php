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

    public function test_private_document_storage_can_select_a_configured_provider_disk(): void
    {
        config([
            'filesystems.private_documents_disk' => 'private-test',
            'filesystems.disks.private-test' => [
                'driver' => 'local',
                'root' => storage_path('framework/testing/private-documents'),
                'serve' => false,
                'throw' => true,
            ],
        ]);

        Storage::fake('private-test');

        self::assertSame('private-test', config('filesystems.private_documents_disk'));
        Storage::disk(config('filesystems.private_documents_disk'))->put('probe.txt', 'provider-neutral');
        self::assertTrue(Storage::disk('private-test')->exists('probe.txt'));
    }
}
