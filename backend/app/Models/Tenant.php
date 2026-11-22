<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'status', 'locale', 'timezone'];

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }
}
