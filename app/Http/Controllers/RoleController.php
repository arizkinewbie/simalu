<?php

namespace App\Http\Controllers;

use App\Constants\RBAC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginate = 10;
        $searchQuery = $request->q;

        if ($request->has('q')) {
            $roles = Role::where('name', 'like', "%{$searchQuery}%")
                ->paginate($paginate)
                ->withQueryString();
        } else {
            $roles = Role::paginate($paginate)->withQueryString();
        }

        return view('dashboard.pages.settings.roles.index', compact('roles', 'searchQuery'));
    }

/**
 * Show the form for creating a new resource.
 */
    public function create()
    {
        $permissions = [];

        foreach (Permission::all() as $value) {
            [$page, $scope] = explode("/", $value->name);

            $permissions[$page][] = [$value->id, $scope];
        }

        return view('dashboard.pages.settings.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array', 'max:11'],
            'permissions.*' => ['nullable', 'integer', 'max:11'],
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $input['name'],
                'guard' => RBAC::GUARD_WEB,
            ]);

            foreach ($input['permissions'] ?? [] as $id) {
                $permission = Permission::findById($id);

                $role->givePermissionTo($permission);
            }

            DB::commit();

            Alert::toast('Role created successfully!', 'success');
        } catch (\Exception $e) {
            Log::error($e);

            DB::rollBack();

            Alert::toast('Role creation failed!', 'error');
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $role = Role::findById($id);

            $role->delete();

            DB::commit();

            Alert::toast('Role deleted successfully!', 'success');
        } catch (\Exception $e) {
            Log::error($e);

            DB::rollBack();

            Alert::toast('Role deletion failed!', 'error');
        }

        return back();
    }
}
