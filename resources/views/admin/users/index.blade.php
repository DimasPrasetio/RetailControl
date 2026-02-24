@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Manajemen User</h2>
        <p class="text-sm text-gray-500 mt-0.5">Kelola akun dan hak akses pengguna sistem</p>
    </div>
    @can('create', \App\Models\User::class)
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Tambah User
        </a>
    @endcan
</div>

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nama</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Username</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Role</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->username }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">
                            {{ $user->role->name->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($user->is_active)
                            <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @can('update', $user)
                                <a href="{{ route('admin.users.edit', $user) }}"
                                    class="text-blue-600 hover:text-blue-800 transition">
                                    Edit
                                </a>
                            @endcan
                            @can('delete', $user)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-800 transition">
                                        Hapus
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        Belum ada user terdaftar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($users->hasPages())
    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endif
@endsection
