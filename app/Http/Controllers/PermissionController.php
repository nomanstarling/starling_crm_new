<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;


class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return response()->json(view('admin.permissions.create')->render());
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'permission' => $permission,
        ]);
    }

    public function edit(Permission $permission)
    {
        return response()->json(view('admin.permissions.edit', compact('permission'))->render());
    }

    public function store(Request $request)
    {
        $permission = Permission::create($request->all());

        notify()->success('Permission created successfully', 'Success');

        return response()->json(['message' => 'Permission created successfully']);
    }

    public function update(Request $request, Permission $permission)
    {
        $permission->update($request->all());

        notify()->success('Permission updated successfully', 'Success');

        return response()->json(['message' => 'Permission updated successfully']);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        notify()->success('Permission deleted successfully', 'Success');
        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
