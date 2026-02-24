@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-800">Audit Log</h2>
    <p class="text-sm text-gray-500 mt-0.5">Riwayat seluruh aktivitas sensitif di sistem</p>
</div>

{{-- Filter form --}}
<div class="bg-white rounded-lg border border-gray-200 p-4 mb-4">
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-wrap gap-3 items-end">

        <div>
            <label for="action" class="block text-xs font-medium text-gray-600 mb-1">Aksi</label>
            <select id="action" name="action"
                class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua</option>
                @foreach (\App\Enums\AuditActionEnum::cases() as $action)
                    <option value="{{ $action->value }}" {{ request('action') === $action->value ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $action->value)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="auditable_type" class="block text-xs font-medium text-gray-600 mb-1">Tipe Objek</label>
            <input type="text" id="auditable_type" name="auditable_type"
                value="{{ request('auditable_type') }}"
                placeholder="cth: App\Models\User"
                class="px-3 py-1.5 border border-gray-300 rounded text-sm w-48 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="date_from" class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
            <input type="date" id="date_from" name="date_from"
                value="{{ request('date_from') }}"
                class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="date_to" class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" id="date_to" name="date_to"
                value="{{ request('date_to') }}"
                class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                Filter
            </button>
            <a href="{{ route('admin.audit-logs.index') }}"
                class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded transition">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Waktu</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">User</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Aksi</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Objek</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">IP</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Detail</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        {{ $log->created_at->format('d M Y H:i:s') }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $log->user?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $actionColor = match($log->action->value) {
                                'create'        => 'bg-green-100 text-green-700',
                                'update'        => 'bg-yellow-100 text-yellow-700',
                                'delete'        => 'bg-red-100 text-red-700',
                                'status_change' => 'bg-purple-100 text-purple-700',
                                'void'          => 'bg-orange-100 text-orange-700',
                                'login'         => 'bg-blue-100 text-blue-700',
                                'logout'        => 'bg-gray-100 text-gray-600',
                                default         => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $actionColor }}">
                            {{ ucfirst(str_replace('_', ' ', $log->action->value)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        @if ($log->auditable_type)
                            {{ class_basename($log->auditable_type) }}
                            <span class="text-gray-400">#{{ $log->auditable_id }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($log->old_values || $log->new_values)
                            <button type="button"
                                class="text-xs text-blue-600 hover:underline"
                                onclick="toggleDetail(this)"
                                data-old="{{ json_encode($log->old_values) }}"
                                data-new="{{ json_encode($log->new_values) }}">
                                Lihat
                            </button>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr class="detail-row hidden bg-gray-50">
                    <td colspan="6" class="px-6 py-3">
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <p class="font-medium text-gray-600 mb-1">Sebelum:</p>
                                <pre class="bg-white border border-gray-200 rounded p-2 overflow-x-auto text-gray-700"></pre>
                            </div>
                            <div>
                                <p class="font-medium text-gray-600 mb-1">Sesudah:</p>
                                <pre class="bg-white border border-gray-200 rounded p-2 overflow-x-auto text-gray-700"></pre>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        Tidak ada audit log ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($logs->hasPages())
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endif

<script>
function toggleDetail(btn) {
    const row = btn.closest('tr').nextElementSibling;
    const pres = row.querySelectorAll('pre');
    const oldData = JSON.parse(btn.dataset.old || 'null');
    const newData = JSON.parse(btn.dataset.new || 'null');

    pres[0].textContent = oldData ? JSON.stringify(oldData, null, 2) : '(kosong)';
    pres[1].textContent = newData ? JSON.stringify(newData, null, 2) : '(kosong)';

    row.classList.toggle('hidden');
    btn.textContent = row.classList.contains('hidden') ? 'Lihat' : 'Tutup';
}
</script>
@endsection
