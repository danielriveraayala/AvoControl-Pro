<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();
        
        // Filter users based on hierarchy - users can only see users they can manage
        if ($authUser->isSuperAdmin()) {
            $query = User::with(['roles']);
        } else {
            $userHierarchy = $authUser->getHighestHierarchyLevel();
            $query = User::with(['roles'])
                ->whereHas('roles', function ($q) use ($userHierarchy) {
                    $q->where('hierarchy_level', '<', $userHierarchy);
                })
                ->orWhere('id', $authUser->id); // Always include current user
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Role filter - only show roles user can manage
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->whereNull('suspended_at');
            } elseif ($status === 'suspended') {
                $query->whereNotNull('suspended_at');
            }
        }
        
        $users = $query->latest()->paginate(20);
        $roles = $authUser->getManageableRoles();
        
        return view('developer.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Only allow creating users if user has sufficient hierarchy
        if (!$user->isSuperAdmin() && $user->getHighestHierarchyLevel() <= 1) {
            return redirect()->route('developer.users.index')
                ->with('error', 'No tienes permisos para crear usuarios.');
        }
        
        $roles = $user->getManageableRoles();
        return view('developer.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'send_welcome_email' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Validate that user can assign all requested roles
        $requestedRoles = Role::whereIn('id', $request->roles)->get();
        foreach ($requestedRoles as $role) {
            if (!$authUser->canAssignRole($role)) {
                return redirect()->back()
                    ->with('error', "No tienes permisos para asignar el rol '{$role->display_name}'.")
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => Role::find($request->roles[0])->name, // Set primary role for legacy compatibility
                'email_verified_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Assign roles
            $roleIds = $request->roles;
            $primaryRoleId = $roleIds[0]; // First role is primary
            
            foreach ($roleIds as $index => $roleId) {
                $user->roles()->attach($roleId, [
                    'is_primary' => $roleId == $primaryRoleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Send welcome email if requested
            if ($request->boolean('send_welcome_email')) {
                // TODO: Implement welcome email functionality
                // Mail::to($user->email)->send(new WelcomeEmail($user, $request->password));
            }

            DB::commit();

            return redirect()->route('developer.users.index')
                ->with('success', "Usuario {$user->name} creado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can view this user
        if (!$authUser->canManageUser($user) && $user->id !== $authUser->id) {
            return redirect()->route('developer.users.index')
                ->with('error', 'No tienes permisos para ver este usuario.');
        }
        
        $user->load(['roles.permissions']);
        
        // Get user activity (example data - implement based on your needs)
        $activityData = [
            'last_login' => $user->updated_at,
            'lots_created' => 0, // Lots don't have user relationship in current schema
            'sales_created' => $user->sales()->count() ?? 0,
            'payments_processed' => $user->payments()->count() ?? 0,
        ];
        
        return view('developer.users.show', compact('user', 'activityData'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return redirect()->route('developer.users.index')
                ->with('error', 'No tienes permisos para editar este usuario.');
        }
        
        $user->load('roles');
        $roles = $authUser->getManageableRoles();
        $userRoleIds = $user->roles->pluck('id')->toArray();
        
        return view('developer.users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return redirect()->back()
                ->with('error', 'No tienes permisos para modificar este usuario.');
        }
        
        // Prevent modifying other super_admin users unless you're the primary developer
        if ($user->hasRole('super_admin') && $authUser->email !== 'developer@avocontrol.com') {
            return redirect()->back()
                ->with('error', 'No tienes permisos para modificar otros usuarios super admin.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user basic info
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Update legacy role field
            $updateData['role'] = Role::find($request->roles[0])->name;

            $user->update($updateData);

            // Validate that user can assign all requested roles
            $requestedRoles = Role::whereIn('id', $request->roles)->get();
            foreach ($requestedRoles as $role) {
                if (!$authUser->canAssignRole($role)) {
                    return redirect()->back()
                        ->with('error', "No tienes permisos para asignar el rol '{$role->display_name}'.")
                        ->withInput();
                }
            }
            
            // Update roles
            $user->roles()->detach();
            $roleIds = $request->roles;
            $primaryRoleId = $roleIds[0];
            
            foreach ($roleIds as $roleId) {
                $user->roles()->attach($roleId, [
                    'is_primary' => $roleId == $primaryRoleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Clear user permissions cache
            $user->clearPermissionsCache();

            DB::commit();

            return redirect()->route('developer.users.index')
                ->with('success', "Usuario {$user->name} actualizado exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return redirect()->back()
                ->with('error', 'No tienes permisos para eliminar este usuario.');
        }
        
        // Prevent deleting super_admin users
        if ($user->hasRole('super_admin')) {
            return redirect()->back()
                ->with('error', 'No se pueden eliminar usuarios super admin por seguridad.');
        }

        // Prevent deleting yourself
        if ($user->id === $authUser->id) {
            return redirect()->back()
                ->with('error', 'No puedes eliminarte a ti mismo.');
        }

        try {
            $userName = $user->name;
            $user->delete();
            
            return redirect()->route('developer.users.index')
                ->with('success', "Usuario {$userName} eliminado exitosamente.");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Assign roles to user.
     */
    public function assignRoles(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos para gestionar este usuario']);
        }
        
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Roles inválidos']);
        }
        
        // Validate that user can assign all requested roles
        $requestedRoles = Role::whereIn('id', $request->roles)->get();
        foreach ($requestedRoles as $role) {
            if (!$authUser->canAssignRole($role)) {
                return response()->json(['success' => false, 'message' => "No tienes permisos para asignar el rol '{$role->display_name}'."]);
            }
        }

        try {
            DB::beginTransaction();

            $user->roles()->detach();
            $roleIds = $request->roles;
            $primaryRoleId = $roleIds[0];
            
            foreach ($roleIds as $roleId) {
                $user->roles()->attach($roleId, [
                    'is_primary' => $roleId == $primaryRoleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update legacy role field
            $user->update(['role' => Role::find($primaryRoleId)->name]);
            $user->clearPermissionsCache();

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Roles asignados exitosamente',
                'roles' => $user->fresh()->roles->pluck('display_name')->toArray()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Suspend user account.
     */
    public function suspend(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos para suspender este usuario']);
        }
        
        if ($user->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'No se pueden suspender usuarios super admin']);
        }

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $request->get('reason', 'Suspendido por administrador'),
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario suspendido exitosamente']);
    }

    /**
     * Activate user account.
     */
    public function activate(User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos para activar este usuario']);
        }
        
        $user->update([
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario activado exitosamente']);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        // Check if user can manage this user
        if (!$authUser->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos para cambiar la contraseña de este usuario']);
        }
        
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Contraseña inválida']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Contraseña restablecida exitosamente']);
    }
}