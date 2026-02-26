@extends('layouts.app')

@section('title', $item->name)
@section('page-title', 'Detail Produk')

@section('content')

<div class="mb-4 flex items-center gap-3">
    <a href="{{ route('admin.items.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Daftar Produk</a>
    @can('update', $item)
        <a href="{{ route('admin.items.edit', $item) }}"
           class="ml-auto rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">Edit</a>
    @endcan
</div>

<div class="grid gap-6 lg:grid-cols-3">

    {{-- Product Info --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200/80">
            <div class="mb-1 flex items-start justify-between">
                <span class="font-mono text-xs text-gray-400">{{ $item->sku_code }}</span>
                @if($item->is_active)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Aktif</span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600"><span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>Nonaktif</span>
                @endif
            </div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $item->name }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Brand:</span> <span class="font-medium">{{ $item->brand?->name ?? '-' }}</span></div>
                <div><span class="text-gray-500">Kategori:</span> <span class="font-medium">{{ $item->category?->name ?? '-' }}</span></div>
                <div><span class="text-gray-500">Satuan Jual:</span> <span class="font-mono font-semibold text-blue-700">{{ $item->baseUom->code }}</span></div>
                <div><span class="text-gray-500">Satuan Beli:</span> <span class="font-mono">{{ $item->purchaseUom?->code ?? '(sama)' }}</span></div>
                <div><span class="text-gray-500">Pack Qty:</span> <span class="font-medium">{{ $item->pack_qty }}</span></div>
                <div><span class="text-gray-500">PPN:</span> <span class="font-medium">{{ $item->tax_included ? 'Sudah termasuk' : 'Tidak termasuk' }}</span></div>
            </div>

            @if($item->attributes_json)
                <hr class="my-4 border-gray-100">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Atribut Teknis</h3>
                <div class="rounded-xl bg-gray-50 p-3 font-mono text-xs text-gray-700 overflow-x-auto">
                    @foreach($item->attributes_json as $key => $val)
                        <div><span class="text-blue-600">{{ $key }}</span>: {{ is_array($val) ? json_encode($val) : $val }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Price Lists --}}
        @if($item->priceListItems->isNotEmpty())
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200/80">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Harga Jual</h3>
                <table class="min-w-full text-sm">
                    <thead><tr class="border-b border-gray-100">
                        <th class="pb-2 text-left text-xs font-semibold text-gray-400">Price List</th>
                        <th class="pb-2 text-right text-xs font-semibold text-gray-400">Harga</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($item->priceListItems as $pli)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $pli->priceList->name }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900">Rp {{ number_format($pli->sell_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Audit Log --}}
    <div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200/80">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Riwayat Perubahan</h3>
            @forelse($auditLogs as $log)
                <div class="mb-3 border-b border-gray-100 pb-3 last:border-0 last:mb-0 last:pb-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-block rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $log->action->value }}</span>
                        <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">oleh: {{ $log->user?->name ?? 'System' }}</p>
                </div>
            @empty
                <p class="text-xs text-gray-400">Belum ada riwayat perubahan.</p>
            @endforelse

            @if($auditLogs->hasPages())
                <div class="mt-3 border-t border-gray-100 pt-3">{{ $auditLogs->links() }}</div>
            @endif
        </div>
    </div>

</div>

@endsection
