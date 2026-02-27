@extends('layouts.app')

@section('title', $item->name)
@section('page-title', 'Detail Produk')

@section('content')

    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('admin.items.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-500 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Daftar Produk
        </a>
        @can('update', $item)
            <a href="{{ route('admin.items.edit', $item) }}"
                class="ml-auto inline-flex items-center gap-1 rounded-xl bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm shadow-amber-100/50 transition hover:bg-amber-100 hover:shadow-md">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Product Info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
                <div class="mb-1 flex items-start justify-between">
                    <span class="font-mono text-xs text-gray-600">{{ $item->sku_code }}</span>
                    @if($item->is_active)
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-red-200/60">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Nonaktif
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $item->name }}</h2>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-600">Brand:</span> <span
                            class="font-medium text-gray-900">{{ $item->brand?->name ?? '-' }}</span></div>
                    <div><span class="text-gray-600">Kategori:</span> <span
                            class="font-medium text-gray-900">{{ $item->category?->name ?? '-' }}</span></div>
                    <div><span class="text-gray-600">Satuan Jual:</span> <span
                            class="font-mono font-semibold text-blue-700">{{ $item->baseUom->code }}</span></div>
                    <div><span class="text-gray-600">Satuan Beli:</span> <span
                            class="font-mono text-gray-800">{{ $item->purchaseUom?->code ?? '(sama)' }}</span></div>
                    <div><span class="text-gray-600">Pack Qty:</span> <span
                            class="font-medium text-gray-900">{{ $item->pack_qty }}</span></div>
                    <div><span class="text-gray-600">PPN:</span> <span
                            class="font-medium text-gray-900">{{ $item->tax_included ? 'Sudah termasuk' : 'Tidak termasuk' }}</span>
                    </div>
                </div>

                @if($item->attributes_json)
                    <hr class="my-4 border-gray-100">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-600">Spesifikasi Produk</h3>
                    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden text-sm">
                        @foreach($item->attributes_json as $key => $val)
                            <div class="grid grid-cols-3 gap-2 px-4 py-2.5 border-b border-gray-100 last:border-0 bg-gray-50/50">
                                <span class="font-medium text-gray-600 col-span-1">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                <span
                                    class="text-gray-900 col-span-2 break-words">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Price Lists --}}
            @if($item->priceListItems->isNotEmpty())
                <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-600">Harga Jual</h3>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="pb-2 text-left text-xs font-semibold text-gray-600">Price List</th>
                                <th class="pb-2 text-right text-xs font-semibold text-gray-600">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($item->priceListItems as $pli)
                                <tr>
                                    <td class="py-2 text-gray-700">{{ $pli->priceList->name }}</td>
                                    <td class="py-2 text-right font-semibold text-gray-900">Rp
                                        {{ number_format($pli->sell_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Audit Log --}}
        <div>
            <div class="rounded-2xl bg-white p-5 shadow-xl shadow-indigo-500/10 border border-slate-300">
                <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-600">Riwayat Perubahan</h3>
                @forelse($auditLogs as $log)
                    <div class="mb-3 border-b border-gray-100 pb-3 last:border-0 last:mb-0 last:pb-0">
                        <div class="flex items-center justify-between gap-2">
                            <span
                                class="inline-block rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $log->action->value }}</span>
                            <span class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-600">oleh: {{ $log->user?->name ?? 'System' }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">Belum ada riwayat perubahan.</p>
                @endforelse

                @if($auditLogs->hasPages())
                    <div class="mt-3 border-t border-gray-100 pt-3">{{ $auditLogs->links() }}</div>
                @endif
            </div>
        </div>

    </div>

@endsection