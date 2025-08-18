<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'managers' => User::where('role', 'manager')->count(),
            'last_login' => User::whereNotNull('last_login_at')->orderBy('last_login_at', 'desc')->first()?->last_login_at?->diffForHumans()
        ];
        return view('users.index', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['member', 'manager'])],
            'password' => 'nullable|string|min:6',
            'status' => ['required', Rule::in(['active', 'suspended'])]
        ]);

        $password = $data['password'] ?: Str::password(10);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'password' => Hash::make($password),
        ]);

        return back()->with('success', 'Compte créé: ' . $user->email . ' / Mot de passe: ' . $password);
    }

    public function update(User $user, Request $request)
    {
        

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['member', 'manager'])],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'password' => 'nullable|string|min:6'
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'password' => $data['password'] ? Hash::make($data['password']) : $user->password,
        ]);

        return back()->with('success', 'Utilisateur mis à jour: ' . $user->email);
    }

    public function resetPassword(User $user, Request $request)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas réinitialiser votre propre mot de passe.');
        }

        $request->validate(['password' => 'nullable|string|min:6']);
        $new = $request->input('password') ?: Str::password(10);
        $user->password = Hash::make($new);
        $user->save();

        return back()->with('success', 'Nouveau mot de passe pour ' . $user->email . ': ' . $new);
    }

    public function changeRole(User $user, Request $request)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $data = $request->validate(['role' => ['required', Rule::in(['member', 'manager'])]]);
        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Rôle mis à jour pour ' . $user->email);
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $email = $user->email;
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé: ' . $email);
    }
}
