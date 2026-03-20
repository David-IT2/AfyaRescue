@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white">Users</h1>
        <a href="{{ route('super-admin.users.create') }}" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Add user</a>
    </div>
    <form method="get" class="flex gap-2 flex-wrap" id="filter-form">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name/email" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 placeholder:text-slate-400" />
        <select name="role" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900">
            <option value="">All roles</option>
            @foreach(['patient','driver','hospital_admin','super_admin'] as $r)
                <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-200 px-3 py-1 text-sm text-slate-900">Filter</button>
        <a href="{{ route('super-admin.users.index') }}" class="rounded-md bg-slate-100 px-3 py-1 text-sm text-slate-700 hover:bg-slate-200">Clear</a>
    </form>
    <script>
        document.getElementById('filter-form').addEventListener('submit', function () {
            this.querySelectorAll('input, select').forEach(function (el) {
                if (el.value === '') el.disabled = true;
            });
        });
    </script>
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-900">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Email</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Role</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Hospital</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($users as $u)
                <tr>
                    <td class="px-4 py-2 text-sm text-slate-900">{{ $u->name }}</td>
                    <td class="px-4 py-2 text-sm text-slate-700">{{ $u->email }}</td>
                    <td class="px-4 py-2 text-sm text-slate-700">{{ $u->role }}</td>
                    <td class="px-4 py-2 text-sm text-slate-700">{{ $u->hospital->name ?? '—' }}</td>
                    <td class="px-4 py-2 flex items-center gap-3">
                        <a href="{{ route('super-admin.users.edit', $u) }}" class="text-red-600 hover:underline text-sm">Edit</a>
                        <form method="POST" action="{{ route('super-admin.users.destroy', $u) }}" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-slate-500 hover:text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-4 text-center text-slate-500">No users.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->withQueryString()->links() }}
</div>

<script>
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this user? This cannot be undone.')) {
                form.submit();
            }
        });
    });
</script>
@endsection