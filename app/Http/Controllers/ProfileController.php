<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000'
        ]);

        $user->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'user' => $user
            ]);
        }

        return redirect()->route('profile.index')
            ->with('success', 'Perfil actualizado correctamente');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'language' => 'required|string|in:es,en',
            'timezone' => 'required|string|max:255',
            'theme' => 'required|string|in:light,dark,auto',
            'email_notifications' => 'boolean',
            'browser_notifications' => 'boolean',
            'inventory_alerts' => 'boolean'
        ]);

        // Convert checkbox values
        $validated['email_notifications'] = $request->has('email_notifications');
        $validated['browser_notifications'] = $request->has('browser_notifications');
        $validated['inventory_alerts'] = $request->has('inventory_alerts');

        // Store settings in user preferences (could be a JSON column or separate table)
        $user->update([
            'preferences' => json_encode($validated)
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada correctamente'
            ]);
        }

        return redirect()->route('profile.index')
            ->with('success', 'Configuración guardada correctamente');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ]);
            }

            return back()->withErrors([
                'current_password' => 'La contraseña actual es incorrecta'
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada correctamente'
            ]);
        }

        return redirect()->route('profile.index')
            ->with('success', 'Contraseña cambiada correctamente');
    }
}