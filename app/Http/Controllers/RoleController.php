<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        $permissions = Permission::all();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function create()
    {
        return response()->json(view('admin.roles.create')->render());
    }

    public function show(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions;
        return response()->json([
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function edit(Role $role)
    {
        return response()->json(view('admin.roles.edit', compact('role'))->render());
    }

    public function store(Request $request)
    {
        $role = Role::create($request->except('permissions'));

        if ($request->has('permissions')) {
            $permissions = $request->input('permissions');
            $role->syncPermissions($permissions);
        }

        notify()->success('Role created successfully', 'Success');

        return response()->json(['message' => 'Role created successfully']);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->except('permissions'));

        if ($request->has('permissions')) {
            $permissions = $request->input('permissions');
            $role->syncPermissions($permissions);
        }

        notify()->success('Role updated successfully', 'Success');

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        notify()->success('Role deleted successfully', 'Success');
        return response()->json(['message' => 'Role deleted successfully']);
    }
}