<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LegalEntity extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'registration_number',
        'legal_name',
        'legal_form',
        'country_code',
        'tax_identifier',
        'base_currency',
        'status',
    ];
}
