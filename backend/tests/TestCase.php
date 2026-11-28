<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // This privileged connection exists only in the test process. It is
        // deliberately absent from application database configuration and .env.
        config([
            'database.connections.pgsql_test_admin' => array_merge(
                config('database.connections.pgsql'),
                [
                    'username' => env('TEST_DB_ADMIN_USERNAME', 'postgres'),
                    'password' => env('TEST_DB_ADMIN_PASSWORD', ''),
                ],
            ),
        ]);

        DB::purge('pgsql_test_admin');
    }
}
