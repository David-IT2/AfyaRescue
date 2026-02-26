<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('hospital:id,name');
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(fn ($qry) => $qry->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }
        $users = $query->orderBy('name')->paginate(20);
        return view('super-admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('super-admin.users.form', [
            'user' => null,
            'hospitals' => Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:patient,driver,hospital_admin,super_admin'],
            'phone' => ['nullable', 'string', 'max:32'],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
        ]);
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'hospital_id' => $validated['hospital_id'] ?? null,
        ]);
        return redirect()->route('super-admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('super-admin.users.form', [
            'user' => $user,
            'hospitals' => Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:patient,driver,hospital_admin,super_admin'],
            'phone' => ['nullable', 'string', 'max:32'],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
        ]);
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'hospital_id' => $validated['hospital_id'] ?? null,
        ]);
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        return redirect()->route('super-admin.users.index')->with('success', 'User updated.');
    }
}
