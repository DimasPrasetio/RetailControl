{{--
  Recursive tree node for stock locations.
  Variables:
    $location     — current StockLocation model
    $allLocations — Collection<id, StockLocation> (full set, in-memory)
    $depth        — int (0 = root, 1 = first child, ...)
--}}
@php
    $children  = $allLocations->where('parent_id', $location->id)->values();
    $hasChildren = $children->count() > 0;
    $isSystem   = $location->isSystemLocation();
    $badgeText  = $isSystem ? $location->system_type : $location->label;
    $badgeColor = match($location->system_type) {
        'BRANCH'    => 'bg-slate-100 text-slate-700',
        'WAREHOUSE' => 'bg-blue-50 text-blue-700',
        default     => 'bg-gray-100 text-gray-600',
    };
@endphp

<div
    x-data="{ open: true }"
    class="{{ $depth > 0 ? 'ml-6 border-l-2 border-gray-100 pl-0' : '' }}"
>
    {{-- Node Row --}}
    <div class="group flex items-center gap-2.5 py-2.5 pr-4 transition-colors hover:bg-blue-50/40
        {{ $depth === 0 ? 'pl-3' : 'pl-3' }}
        {{ !$location->is_active ? 'opacity-60' : '' }}">

        {{-- Indent spacer lines for depth > 0 --}}
        @if ($depth > 0)
            <div class="flex w-4 flex-shrink-0 items-center" aria-hidden="true">
                <div class="h-px w-4 bg-gray-200"></div>
            </div>
        @endif

        {{-- Toggle chevron --}}
        @if ($hasChildren)
            <button @click="open = !open"
                class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="open ? 'rotate-90' : ''"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        @else
            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center" aria-hidden="true">
                <span class="h-1 w-1 rounded-full bg-gray-300"></span>
            </span>
        @endif

        {{-- Node icon --}}
        @if ($isSystem)
            <svg class="h-4 w-4 flex-shrink-0 {{ $location->system_type === 'WAREHOUSE' ? 'text-blue-400' : 'text-slate-400' }}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
            </svg>
        @else
            <svg class="h-4 w-4 flex-shrink-0 text-gray-400"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        @endif

        {{-- Name & Code --}}
        <div class="flex min-w-0 flex-1 items-center gap-2.5">
            <span class="text-sm font-medium text-gray-900 {{ !$location->is_active ? 'line-through text-gray-400' : '' }}">
                {{ $location->name }}
            </span>
            <span class="font-mono text-xs text-gray-400">{{ $location->code }}</span>
        </div>

        {{-- Badge: system_type untuk lokasi sistem, label untuk lokasi user (hanya jika diisi) --}}
        @if ($badgeText)
            <span class="flex-shrink-0 rounded-md px-2 py-0.5 text-xs font-semibold {{ $badgeColor }}">
                {{ $badgeText }}
            </span>
        @endif

        {{-- Status badge --}}
        @if ($location->is_active)
            <span class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
            </span>
        @else
            <span class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200/60">
                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Nonaktif
            </span>
        @endif

        {{-- Actions --}}
        <div class="flex flex-shrink-0 items-center gap-1.5">
            @can('update', $location)
                <a href="{{ route('admin.stock-locations.edit', $location) }}"
                    class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            @endcan
            @if (!$isSystem)
                @can('deactivate', $location)
                    @if ($location->is_active)
                        <form method="POST" action="{{ route('admin.stock-locations.deactivate', $location) }}"
                            onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan lokasi {{ addslashes($location->name) }}?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-2.5 py-1.5 text-xs font-semibold text-orange-700 shadow-sm transition hover:bg-orange-100">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Nonaktifkan
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.stock-locations.reactivate', $location) }}"
                            onsubmit="confirmAction(event, this, 'Aktifkan Lokasi', 'Aktifkan kembali lokasi {{ addslashes($location->name) }}?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aktifkan
                            </button>
                        </form>
                    @endif
                @endcan
                @can('delete', $location)
                    <form id="delete-stockloc-{{ $location->id }}" method="POST"
                        action="{{ route('admin.stock-locations.destroy', $location) }}" style="display:none;">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-stockloc-{{ $location->id }}', itemName: '{{ addslashes($location->name) }}' } }))"
                        class="inline-flex items-center gap-1 rounded-lg border border-red-700 bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
                @endcan
            @else
                <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-500">
                    Lokasi Sistem
                </span>
            @endif
        </div>
    </div>

    {{-- Recursive children --}}
    @if ($hasChildren)
        <div x-show="open"
            x-transition:enter="transition-opacity duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            @foreach ($children as $child)
                @include('admin.stock-locations._node', [
                    'location'     => $child,
                    'allLocations' => $allLocations,
                    'depth'        => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
