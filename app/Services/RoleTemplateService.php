<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

class RoleTemplateService
{
    /**
     * Get all roles that are defined as templates by the Super Admin.
     * These are roles that are NOT tenant-specific (currently all roles).
     */
    public function getTemplates(): Collection
    {
        // Currently, all roles are global.
        // We exclude 'super_admin' from templates available to school admins.
        return Role::where('name', '!=', 'super_admin')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Verify if a role is a valid template.
     */
    public function isValidTemplate(string $roleName): bool
    {
        return Role::where('name', $roleName)
            ->where('name', '!=', 'super_admin')
            ->exists();
    }
}
