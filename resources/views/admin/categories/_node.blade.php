{{--
  Recursive tree node for categories.
  Variables:
    $category     — current Category model
    $allCategories — Collection<id, Category> (full set, in-memory)
    $depth        — int (0 = root, 1 = first child, ...)
--}}
@php
    $children    = $allCategories->where('parent_id', $category->id)->values();
    $hasChildren = $children->count() > 0;
@endphp

<div
    x-data="{ open: true }"
    class="{{ $depth > 0 ? 'ml-6 border-l-2 border-gray-100 pl-0' : '' }}"
>
    {{-- Node Row --}}
    <div class="group flex items-center gap-2.5 py-2.5 pr-4 transition-colors hover:bg-indigo-50/40 pl-3
        {{ !$category->is_active ? 'opacity-60' : '' }}">

        {{-- Indent connector for depth > 0 --}}
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

        {{-- Icon: folder if has children, tag if leaf --}}
        @if ($hasChildren)
            <svg class="h-4 w-4 flex-shrink-0 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
        @else
            <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        @endif

        {{-- Name, code, children count --}}
        <div class="flex min-w-0 flex-1 items-center gap-2.5">
            <span class="text-sm font-medium text-gray-900 {{ !$category->is_active ? 'line-through text-gray-400' : '' }}">
                {{ $category->name }}
            </span>
            @if ($category->code)
                <span class="font-mono text-xs text-gray-400">{{ $category->code }}</span>
            @endif
            @if ($hasChildren)
                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600">
                    {{ $children->count() }} sub
                </span>
            @endif
        </div>

        {{-- Status badge --}}
        @if ($category->is_active)
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
            @can('update', $category)
                <a href="{{ route('admin.categories.edit', $category) }}"
                    class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                    Edit
                </a>
            @endcan
            @if ($category->is_active)
                @can('deactivate', $category)
                    <form method="POST" action="{{ route('admin.categories.deactivate', $category) }}"
                        onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan kategori {{ addslashes($category->name) }}?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="rounded-lg bg-orange-50 px-2.5 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100">
                            Nonaktifkan
                        </button>
                    </form>
                @endcan
            @endif
            @can('delete', $category)
                <form id="delete-category-{{ $category->id }}" method="POST"
                    action="{{ route('admin.categories.destroy', $category) }}" style="display:none;">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-category-{{ $category->id }}', itemName: '{{ addslashes($category->name) }}' } }))"
                    class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            @endcan
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
                @include('admin.categories._node', [
                    'category'      => $child,
                    'allCategories' => $allCategories,
                    'depth'         => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
