@extends('layouts.app')

@section('title', 'Atribut Produk')
@section('page-title', 'Definisi Atribut Produk')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600">Kelola field dinamis yang dapat dipasang ke kategori produk.</p>
    @can('create', \App\Models\AttributeDefinition::class)
        <a href="{{ route('admin.attribute-definitions.create', array_filter(['tenant_id' => $selectedTenantId])) }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Atribut
        </a>
    @endcan
</div>

<div class="mb-5 rounded-2xl border border-slate-300 bg-white p-4 shadow-xl shadow-indigo-500/10">
    <form method="GET" action="{{ route('admin.attribute-definitions.index') }}" class="flex flex-wrap items-end gap-3">
        @if(auth()->user()->isPlatformAdmin())
            <div class="min-w-[220px]">
                <label class="mb-1 block text-xs font-medium text-gray-600">Tenant</label>
                <select name="tenant_id"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua Tenant</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ (string) $selectedTenantId === (string) $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="min-w-[240px] flex-1">
            <label class="mb-1 block text-xs font-medium text-gray-600">Pencarian</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari key, label, atau tipe data..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                Filter
            </button>
            @if(request()->hasAny(['tenant_id', 'q']))
                <a href="{{ route('admin.attribute-definitions.index') }}"
                   class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-800">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-slate-50">
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Atribut</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Tipe</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Kategori</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Wajib</th>
                    <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($attributeDefinitions as $attributeDefinition)
                    <tr class="hover:bg-blue-50/40">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $attributeDefinition->label }}</div>
                            <div class="text-xs font-mono text-gray-500">{{ $attributeDefinition->key }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $attributeDefinition->unit ?: '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ strtoupper($attributeDefinition->data_type) }}</td>
                        <td class="px-5 py-4 text-gray-600">
                            @if($attributeDefinition->categories->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($attributeDefinition->categories as $category)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum dipasang</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $attributeDefinition->is_required ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/60' : 'bg-gray-100 text-gray-600' }}">
                                {{ $attributeDefinition->is_required ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('update', $attributeDefinition)
                                    <a href="{{ route('admin.attribute-definitions.edit', $attributeDefinition) }}"
                                       class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        Edit
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500">Belum ada definisi atribut.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($attributeDefinitions->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $attributeDefinitions->links() }}</div>
    @endif
</div>

@endsection
