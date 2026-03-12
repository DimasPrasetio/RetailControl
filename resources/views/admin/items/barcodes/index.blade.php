@extends('layouts.app')

@section('title', 'Kelola Barcode — ' . $item->name)
@section('page-title', 'Kelola Barcode')
@section('breadcrumb', $item->name)

@section('content')

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.items.show', $item) }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-500 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Detail Produk
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Daftar Barcode --}}
        <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
            <div class="mb-1">
                <span class="font-mono text-xs text-gray-500">{{ $item->sku_code }}</span>
            </div>
            <h2 class="text-base font-semibold text-gray-900 mb-4">{{ $item->name }}</h2>

            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-600">
                Daftar Barcode / QR Code
                <span class="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                    {{ $item->barcodes->count() }}
                </span>
            </h3>

            @forelse($item->barcodes as $barcode)
                <div
                    class="mb-3 flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- QR icon --}}
                        <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12v.01M12 4h.01M4 4h4v4H4V4zm12 0h4v4h-4V4zM4 16h4v4H4v-4z" />
                        </svg>
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-semibold text-gray-800 truncate">{{ $barcode->barcode }}</p>
                            @if($barcode->itemUnit)
                                <p class="text-xs text-gray-500">Satuan: {{ $barcode->itemUnit->uom->code }}</p>
                            @else
                                <p class="text-xs text-gray-400">Semua satuan</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-shrink-0 items-center gap-2">
                        @if($barcode->is_primary)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Utama
                            </span>
                        @else
                            <form method="POST" action="{{ route('admin.items.barcodes.set-primary', [$item, $barcode]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 shadow-sm transition hover:border-indigo-300 hover:text-indigo-600"
                                    title="Set sebagai barcode utama">
                                    Set Utama
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.items.barcodes.destroy', [$item, $barcode]) }}"
                            onsubmit="return confirmAction(event, this, 'Hapus Barcode?', 'Barcode ini akan dihapus permanen.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-red-400 transition hover:bg-red-50 hover:text-red-600"
                                title="Hapus barcode">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12v.01M12 4h.01M4 4h4v4H4V4zm12 0h4v4h-4V4zM4 16h4v4H4v-4z" />
                    </svg>
                    <p class="text-sm text-gray-400">Belum ada barcode untuk produk ini.</p>
                    <p class="text-xs text-gray-400 mt-0.5">Tambahkan barcode pertama di form sebelah kanan.</p>
                </div>
            @endforelse
        </div>

        {{-- Form Tambah Barcode --}}
        <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
            <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-600">Tambah Barcode Baru</h3>

            <form method="POST" action="{{ route('admin.items.barcodes.store', $item) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="barcode" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Barcode / QR Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="barcode" name="barcode" value="{{ old('barcode') }}"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-gray-900 transition placeholder-gray-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('barcode') border-red-400 bg-red-50 @enderror"
                        placeholder="Scan atau ketik barcode" autofocus>
                    @error('barcode')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Barcode harus unik di seluruh produk dalam tenant ini.</p>
                </div>

                <div>
                    <label for="item_unit_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Satuan <span class="text-xs font-normal text-gray-500">(opsional)</span>
                    </label>
                    <select id="item_unit_id" name="item_unit_id"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('item_unit_id') border-red-400 bg-red-50 @enderror">
                        <option value="">- Berlaku untuk semua satuan -</option>
                        @foreach($item->itemUnits as $unit)
                            <option value="{{ $unit->id }}" {{ old('item_unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->uom->code }}
                                @if($unit->is_base) (satuan dasar) @endif
                                @if($unit->is_default_sale) (default jual) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('item_unit_id')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Tentukan satuan spesifik jika barcode hanya berlaku untuk satuan
                        tertentu (mis. BOX vs PCS).</p>
                </div>

                <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex h-5 items-center">
                        <input type="hidden" name="is_primary" value="0">
                        <input type="checkbox" id="is_primary" name="is_primary" value="1" {{ old('is_primary') ? 'checked' : ($item->barcodes->count() === 0 ? 'checked' : '') }}
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-400/30">
                    </div>
                    <div>
                        <label for="is_primary" class="cursor-pointer text-sm font-medium text-gray-700">Jadikan barcode
                            utama</label>
                        <p class="text-xs text-gray-500">Barcode utama digunakan sebagai referensi scan default di POS</p>
                    </div>
                </div>

                @if ($errors->any() && !$errors->has('barcode') && !$errors->has('item_unit_id'))
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end pt-1">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Barcode
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection