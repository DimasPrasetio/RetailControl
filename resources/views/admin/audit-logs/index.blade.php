@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')

{{-- ── Subtitle ─────────────────────────────────────────────────────────── --}}
<p class="mb-6 text-sm text-gray-500">Riwayat seluruh aktivitas sensitif yang tercatat di sistem</p>

{{-- ── Filter card ──────────────────────────────────────────────────────── --}}
<div class="mb-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200/80">
    <div class="border-b border-gray-100 px-5 py-3.5">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Filter Log</p>
    </div>
    <form method="GET" action="{{ route('admin.audit-logs.index') }}"
          class="flex flex-wrap items-end gap-4 px-5 py-4">

        {{-- Aksi --}}
        <div class="min-w-[140px]">
            <label for="action" class="mb-1.5 block text-xs font-medium text-gray-600">Aksi</label>
            <select id="action" name="action"
                    class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900
                           focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20">
                <option value="">Semua aksi</option>
                @foreach (\App\Enums\AuditActionEnum::cases() as $act)
                    <option value="{{ $act->value }}" {{ request('action') === $act->value ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $act->value)) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tipe Objek --}}
        <div class="min-w-[200px]">
            <label for="auditable_type" class="mb-1.5 block text-xs font-medium text-gray-600">Tipe Objek</label>
            <input type="text" id="auditable_type" name="auditable_type"
                   value="{{ request('auditable_type') }}"
                   placeholder="cth: App\Models\User"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900
                          placeholder-gray-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20">
        </div>

        {{-- Dari Tanggal --}}
        <div>
            <label for="date_from" class="mb-1.5 block text-xs font-medium text-gray-600">Dari Tanggal</label>
            <input type="date" id="date_from" name="date_from"
                   value="{{ request('date_from') }}"
                   class="block rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900
                          focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20">
        </div>

        {{-- Sampai Tanggal --}}
        <div>
            <label for="date_to" class="mb-1.5 block text-xs font-medium text-gray-600">Sampai Tanggal</label>
            <input type="date" id="date_to" name="date_to"
                   value="{{ request('date_to') }}"
                   class="block rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900
                          focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20">
        </div>

        {{-- Actions --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
            @if(request()->hasAny(['action', 'auditable_type', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:border-gray-300 hover:text-gray-900">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </a>
            @endif
        </div>

    </form>
</div>

{{-- ── Table card ───────────────────────────────────────────────────────── --}}
<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200/80">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Waktu</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Pengguna</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Objek</th>
                    <th class="hidden px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 md:table-cell">IP</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="audit-tbody">
                @forelse ($logs as $log)
                    {{-- Main row --}}
                    <tr class="transition hover:bg-gray-50/60" data-log-id="{{ $log->id }}">
                        <td class="whitespace-nowrap px-5 py-4">
                            <p class="font-medium text-gray-800">{{ $log->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @if ($log->user)
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-[10px] font-bold text-white">
                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-gray-700">{{ $log->user->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $badge = match($log->action->value) {
                                    'create'        => 'bg-green-50 text-green-700 ring-1 ring-green-200',
                                    'update'        => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
                                    'delete'        => 'bg-red-50 text-red-700 ring-1 ring-red-200',
                                    'status_change' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200',
                                    'void'          => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
                                    'login'         => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
                                    'logout'        => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
                                    default         => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ ucfirst(str_replace('_', ' ', $log->action->value)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if ($log->auditable_type)
                                <span class="font-medium text-gray-700">{{ class_basename($log->auditable_type) }}</span>
                                <span class="ml-1 text-xs text-gray-400">#{{ $log->auditable_id }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="hidden px-5 py-4 text-xs text-gray-400 md:table-cell">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if ($log->old_values || $log->new_values)
                                <button type="button"
                                        onclick="toggleDetail({{ $log->id }}, this)"
                                        data-old="{{ json_encode($log->old_values) }}"
                                        data-new="{{ json_encode($log->new_values) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 transition hover:border-blue-300 hover:text-blue-600">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat
                                </button>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    {{-- Detail expandable row --}}
                    <tr id="detail-{{ $log->id }}" class="hidden bg-blue-50/40">
                        <td colspan="6" class="px-5 py-4">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div>
                                    <p class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                        <span class="inline-block h-2 w-2 rounded-full bg-red-400"></span>
                                        Sebelum
                                    </p>
                                    <pre id="old-{{ $log->id }}" class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-700 leading-relaxed"></pre>
                                </div>
                                <div>
                                    <p class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                        <span class="inline-block h-2 w-2 rounded-full bg-green-400"></span>
                                        Sesudah
                                    </p>
                                    <pre id="new-{{ $log->id }}" class="overflow-x-auto rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-700 leading-relaxed"></pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-400">Belum ada audit log ditemukan</p>
                            @if(request()->hasAny(['action', 'auditable_type', 'date_from', 'date_to']))
                                <p class="mt-1 text-xs text-gray-400">Coba ubah filter pencarian</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination footer --}}
    @if ($logs->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<script>
function toggleDetail(id, btn) {
    const row     = document.getElementById('detail-' + id);
    const oldPre  = document.getElementById('old-' + id);
    const newPre  = document.getElementById('new-' + id);
    const oldData = JSON.parse(btn.dataset.old || 'null');
    const newData = JSON.parse(btn.dataset.new || 'null');
    const isHidden = row.classList.contains('hidden');

    if (isHidden) {
        oldPre.textContent = oldData ? JSON.stringify(oldData, null, 2) : '(kosong)';
        newPre.textContent = newData ? JSON.stringify(newData, null, 2) : '(kosong)';
        row.classList.remove('hidden');
        btn.innerHTML = `<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Tutup`;
        btn.classList.add('border-blue-300', 'text-blue-600');
        btn.classList.remove('text-gray-600');
    } else {
        row.classList.add('hidden');
        btn.innerHTML = `<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Lihat`;
        btn.classList.remove('border-blue-300', 'text-blue-600');
        btn.classList.add('text-gray-600');
    }
}
</script>

@endsection
