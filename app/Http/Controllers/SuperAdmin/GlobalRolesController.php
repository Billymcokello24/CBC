<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\RoleTemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GlobalRolesController extends Controller
{
    protected $roleTemplateService;

    public function __construct(RoleTemplateService $roleTemplateService)
    {
        $this->roleTemplateService = $roleTemplateService;
    }

    public function index()
    {
        return Inertia::render('super-admin/roles/Index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        
        Role::create(['name' => $request->name, 'guard_name' => 'web']);
        
        return back()->with('success', 'Global role template created.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate(['permissions' => 'array']);
        
        $role->syncPermissions($request->permissions);
        
        return back()->with('success', 'Role template permissions updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super_admin') {
            return back()->with('error', 'Cannot delete super_admin role.');
        }
        
        $role->delete();
        return back()->with('success', 'Role template deleted.');
    }
}
