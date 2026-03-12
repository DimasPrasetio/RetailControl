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
                    <div><span class="text-gray-600">Satuan Dasar Stok:</span> <span
                            class="font-mono font-semibold text-blue-700">{{ $item->baseUom->code }}</span></div>
                    <div><span class="text-gray-600">Default Jual:</span> <span
                            class="font-mono text-gray-800">{{ $item->sellingUom?->code ?? $item->baseUom->code }}</span>
                    </div>
                    <div><span class="text-gray-600">Default Beli:</span> <span
                            class="font-mono text-gray-800">{{ $item->purchaseUom?->code ?? '-' }}</span></div>
                    <div><span class="text-gray-600">Konversi Beli Default:</span> <span
                            class="font-medium text-gray-900">{{ $item->pack_qty }}</span></div>
                    <div><span class="text-gray-600">PPN:</span> <span
                            class="font-medium text-gray-900">{{ $item->tax_included ? 'Sudah termasuk' : 'Tidak termasuk' }}</span>
                    </div>
                </div>

                @if($item->itemUnits->isNotEmpty())
                    <hr class="my-4 border-gray-100">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-600">Satuan Produk</h3>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="border-b border-gray-100 text-left">
                                    <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Satuan
                                    </th>
                                    <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Konversi
                                        ke Dasar</th>
                                    <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Peran
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($item->itemUnits as $unit)
                                    <tr>
                                        <td class="px-4 py-2 font-mono font-semibold text-gray-900">{{ $unit->uom->code }}</td>
                                        <td class="px-4 py-2 text-gray-700">{{ $unit->conversion_qty }}</td>
                                        <td class="px-4 py-2 text-gray-700">
                                            @php
                                                $badges = [];
                                                if ($unit->is_base)
                                                    $badges[] = 'stok dasar';
                                                if ($unit->allow_sale)
                                                    $badges[] = 'jual';
                                                if ($unit->allow_purchase)
                                                    $badges[] = 'beli';
                                                if ($unit->is_default_sale)
                                                    $badges[] = 'default jual';
                                                if ($unit->is_default_purchase)
                                                    $badges[] = 'default beli';
                                            @endphp
                                            {{ implode(' • ', $badges) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

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
                                <th class="pb-2 text-left text-xs font-semibold text-gray-600">Satuan</th>
                                <th class="pb-2 text-right text-xs font-semibold text-gray-600">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($item->priceListItems as $pli)
                                <tr>
                                    <td class="py-2 text-gray-700">{{ $pli->priceList->name }}</td>
                                    <td class="py-2 text-gray-700 font-mono">
                                        {{ $pli->priceUom?->code ?? $item->sellingUom?->code ?? $item->baseUom->code }}</td>
                                    <td class="py-2 text-right font-semibold text-gray-900">Rp
                                        {{ number_format($pli->sell_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Barcode / QR Code --}}
            <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Barcode / QR Code
                        <span class="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                            {{ $item->barcodes->count() }}
                        </span>
                    </h3>
                    @can('manageBarcodes', $item)
                        <a href="{{ route('admin.items.barcodes.index', $item) }}"
                            class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Kelola Barcode
                        </a>
                    @endcan
                </div>
                @forelse($item->barcodes as $barcode)
                    <div
                        class="mb-2 flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 last:mb-0">
                        <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12v.01M12 4h.01M4 4h4v4H4V4zm12 0h4v4h-4V4zM4 16h4v4H4v-4z" />
                        </svg>
                        <span class="font-mono text-sm text-gray-800 flex-1 truncate">{{ $barcode->barcode }}</span>
                        @if($barcode->itemUnit)
                            <span class="text-xs text-gray-500 font-mono">{{ $barcode->itemUnit->uom->code }}</span>
                        @endif
                        @if($barcode->is_primary)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Utama
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Belum ada barcode.
                        @can('manageBarcodes', $item)
                            <a href="{{ route('admin.items.barcodes.index', $item) }}"
                                class="text-indigo-600 hover:underline">Tambah sekarang</a>
                        @endcan
                    </p>
                @endforelse
            </div>
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