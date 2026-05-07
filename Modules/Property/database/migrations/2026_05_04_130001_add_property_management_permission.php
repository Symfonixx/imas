<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        // Avoid inserting before Core SQL seed runs (empty DB + fresh install).
        $hasBaseline = Permission::query()
            ->where('name', 'Corporate Management')
            ->exists();

        if (! $hasBaseline) {
            return;
        }

        $permission = Permission::firstOrCreate(
            ['name' => 'Property Management', 'guard_name' => 'web'],
        );

        Role::query()
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->first()
            ?->givePermissionTo($permission);
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        Permission::query()
            ->where('name', 'Property Management')
            ->where('guard_name', 'web')
            ->delete();
    }
};
