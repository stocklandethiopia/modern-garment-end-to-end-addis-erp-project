<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:erp', function (): void {
    $this->info('Modern Garment Addis ERP API foundation');
    $this->line('Database: PostgreSQL 18');
    $this->line('Tenant isolation: transaction-local context + Row-Level Security');
})->purpose('Display ERP application foundation information');
