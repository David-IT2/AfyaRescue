@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Users</h1>
        <a href="{{ route('super-admin.users.create') }}" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Add user</a>
    </div>
    <form method="get" class="flex gap-2 flex-wrap">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name/email" class="rounded-md border border-slate-300 px-2 py-1 text-sm" />
        <select name="role" class="rounded-md border border-slate-300 px-2 py-1 text-sm">
            <option value="">All roles</option>
            @foreach(['patient','driver','hospital_admin','super_admin'] as $r)
                <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-200 px-3 py-1 text-sm">Filter</button>
    </form>
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Email</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Role</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Hospital</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($users as $u)
                <tr><td class="px-4 py-2 text-sm">{{ $u->name }}</td><td class="px-4 py-2 text-sm">{{ $u->email }}</td><td class="px-4 py-2 text-sm">{{ $u->role }}</td><td class="px-4 py-2 text-sm">{{ $u->hospital->name ?? '—' }}</td><td class="px-4 py-2"><a href="{{ route('super-admin.users.edit', $u) }}" class="text-red-600 hover:underline">Edit</a></td></tr>
                @empty
                <tr><td colspan="5" class="px-4 py-4 text-center text-slate-500">No users.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->withQueryString()->links() }}
</div>
@endsection
