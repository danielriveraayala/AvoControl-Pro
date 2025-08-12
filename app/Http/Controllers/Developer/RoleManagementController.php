<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\RoleAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RoleManagementController extends Controller
{
    /**
     * Display roles management interface.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Filter roles based on user's hierarchy level
        if ($user->isSuperAdmin()) {
            $roles = Role::with('permissions', 'users')
                ->orderBy('hierarchy_level', 'desc')
                ->get();
        } else {
            $userHierarchy = $user->getHighestHierarchyLevel();
            $roles = Role::with('permissions', 'users')
                ->where('hierarchy_level', '<=', $userHierarchy)
                ->orderBy('hierarchy_level', 'desc')
                ->get();
        }
        
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        return view('developer.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $user = auth()->user();
        
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        // Limit hierarchy level based on user's level
        if ($user->isSuperAdmin()) {
            $maxHierarchy = Role::max('hierarchy_level') ?? 0;
        } else {
            $maxHierarchy = min($user->getHighestHierarchyLevel() - 1, Role::max('hierarchy_level') ?? 0);
        }
        
        return view('developer.roles.create', compact('permissions', 'maxHierarchy'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'hierarchy_level' => 'required|integer|min:1|max:99',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            'name.regex' => 'El nombre del rol debe contener solo letras minúsculas y guiones bajos.'
        ]);

        $user = auth()->user();
        
        // Validate hierarchy level restrictions
        if (!$user->isSuperAdmin()) {
            $userHierarchy = $user->getHighestHierarchyLevel();
            if ($request->hierarchy_level >= $userHierarchy) {
                return back()->withInput()
                    ->with('error', 'No puedes crear un rol con nivel de jerarquía igual o superior al tuyo.');
            }
        }

        DB::beginTransaction();
        
        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'hierarchy_level' => $request->hierarchy_level,
                'is_system' => false
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->attach($request->permissions);
            }

            // Clear permissions cache for all users
            $this->clearAllUsersPermissionsCache();

            // Create audit log
            RoleAudit::log('created', $role, null, [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'hierarchy_level' => $role->hierarchy_level,
                'permissions' => $request->permissions ?? []
            ]);

            DB::commit();

            Log::info('Role created', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'created_by' => auth()->user()->email
            ]);

            return redirect()->route('developer.roles.index')
                ->with('success', 'Rol creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating role', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->email
            ]);
            
            return back()->withInput()
                ->with('error', 'Error al crear el rol: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $user = auth()->user();
        
        // Check if user can view this role
        if (!$user->canManageRole($role)) {
            return redirect()->route('developer.roles.index')
                ->with('error', 'No tienes permisos para ver este rol.');
        }
        
        $role->load('permissions', 'users');
        
        $allPermissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        return view('developer.roles.show', compact('role', 'allPermissions'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $user = auth()->user();
        
        // Check if user can manage this role
        if (!$user->canManageRole($role)) {
            return redirect()->route('developer.roles.index')
                ->with('error', 'No tienes permisos para editar este rol.');
        }
        
        // Prevent editing system roles
        if ($role->is_system) {
            return redirect()->route('developer.roles.index')
                ->with('warning', 'Los roles del sistema no pueden ser editados.');
        }

        $role->load('permissions');
        
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        return view('developer.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $user = auth()->user();
        
        // Check if user can manage this role
        if (!$user->canManageRole($role)) {
            return redirect()->route('developer.roles.index')
                ->with('error', 'No tienes permisos para editar este rol.');
        }
        
        // Prevent updating system roles
        if ($role->is_system) {
            return redirect()->route('developer.roles.index')
                ->with('warning', 'Los roles del sistema no pueden ser modificados.');
        }

        $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'hierarchy_level' => 'required|integer|min:1|max:99',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Validate hierarchy level restrictions
        if (!$user->isSuperAdmin()) {
            $userHierarchy = $user->getHighestHierarchyLevel();
            if ($request->hierarchy_level >= $userHierarchy) {
                return back()->withInput()
                    ->with('error', 'No puedes asignar un nivel de jerarquía igual o superior al tuyo.');
            }
        }

        // Store old values for audit
        $oldValues = [
            'display_name' => $role->display_name,
            'description' => $role->description,
            'hierarchy_level' => $role->hierarchy_level,
            'permissions' => $role->permissions->pluck('id')->toArray()
        ];

        DB::beginTransaction();
        
        try {
            $role->update([
                'display_name' => $request->display_name,
                'description' => $request->description,
                'hierarchy_level' => $request->hierarchy_level
            ]);

            // Sync permissions
            $role->permissions()->sync($request->permissions ?? []);

            // Clear permissions cache for all users with this role
            $this->clearRoleUsersPermissionsCache($role);

            // Create audit log
            RoleAudit::log('updated', $role, $oldValues, [
                'display_name' => $request->display_name,
                'description' => $request->description,
                'hierarchy_level' => $request->hierarchy_level,
                'permissions' => $request->permissions ?? []
            ]);

            DB::commit();

            Log::info('Role updated', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'updated_by' => auth()->user()->email
            ]);

            return redirect()->route('developer.roles.show', $role)
                ->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating role', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
                'user' => auth()->user()->email
            ]);
            
            return back()->withInput()
                ->with('error', 'Error al actualizar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        $user = auth()->user();
        
        // Check if user can manage this role
        if (!$user->canManageRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar este rol.'
            ], 403);
        }
        
        // Prevent deleting system roles
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Los roles del sistema no pueden ser eliminados.'
            ], 403);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un rol que tiene usuarios asignados.'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            $roleName = $role->name;
            
            // Create audit log before deletion
            RoleAudit::log('deleted', $role, [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'hierarchy_level' => $role->hierarchy_level,
                'permissions' => $role->permissions->pluck('id')->toArray()
            ]);
            
            $role->permissions()->detach();
            $role->delete();

            DB::commit();

            Log::info('Role deleted', [
                'role_name' => $roleName,
                'deleted_by' => auth()->user()->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting role', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
                'user' => auth()->user()->email
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update role permissions via AJAX.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $user = auth()->user();
        
        // Check if user can manage this role
        if (!$user->canManageRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar los permisos de este rol.'
            ], 403);
        }
        
        // Prevent updating system roles except super_admin
        if ($role->is_system && $role->name !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Los permisos de roles del sistema no pueden ser modificados.'
            ], 403);
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Store old permissions for audit
        $oldPermissions = $role->permissions->pluck('id')->toArray();

        DB::beginTransaction();
        
        try {
            $role->permissions()->sync($request->permissions ?? []);

            // Clear permissions cache for all users with this role
            $this->clearRoleUsersPermissionsCache($role);

            // Create audit log for permission changes
            RoleAudit::log('permissions_changed', $role, 
                ['permissions' => $oldPermissions], 
                ['permissions' => $request->permissions ?? []]
            );

            DB::commit();

            Log::info('Role permissions updated', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions_count' => count($request->permissions ?? []),
                'updated_by' => auth()->user()->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permisos actualizados exitosamente.',
                'permissions_count' => $role->permissions()->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating role permissions', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
                'user' => auth()->user()->email
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone an existing role.
     */
    public function clone(Role $role)
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $maxHierarchy = Role::max('hierarchy_level') ?? 0;
        
        return view('developer.roles.create', [
            'permissions' => $permissions,
            'maxHierarchy' => $maxHierarchy,
            'clonedFrom' => $role,
            'selectedPermissions' => $rolePermissions
        ]);
    }

    /**
     * Get role details via AJAX.
     */
    public function getDetails($roleId)
    {
        try {
            Log::info('Getting role details', [
                'role_id' => $roleId,
                'user' => auth()->user()->email ?? 'not authenticated'
            ]);
            
            // Manually find the role instead of using route model binding
            $role = Role::find($roleId);
            
            if (!$role) {
                Log::warning('Role not found', [
                    'role_id' => $roleId,
                    'user' => auth()->user()->email ?? 'not authenticated'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            $role->load('permissions', 'users');
            
            $response = [
                'success' => true,
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'hierarchy_level' => $role->hierarchy_level,
                    'is_system' => $role->is_system,
                    'users_count' => $role->users->count(),
                    'permissions_count' => $role->permissions->count(),
                    'permissions' => $role->permissions->groupBy('module')
                ]
            ];
            
            Log::info('Role details retrieved successfully', [
                'role_id' => $role->id,
                'permissions_count' => $role->permissions->count()
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error getting role details', [
                'role_id' => $roleId ?? 'unknown',
                'error' => $e->getMessage(),
                'user' => auth()->user()->email ?? 'not authenticated'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles del rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear permissions cache for all users.
     */
    private function clearAllUsersPermissionsCache()
    {
        $users = User::all();
        foreach ($users as $user) {
            Cache::forget('user_permissions_' . $user->id);
        }
    }

    /**
     * Clear permissions cache for users with specific role.
     */
    private function clearRoleUsersPermissionsCache(Role $role)
    {
        $users = $role->users;
        foreach ($users as $user) {
            Cache::forget('user_permissions_' . $user->id);
        }
    }
}