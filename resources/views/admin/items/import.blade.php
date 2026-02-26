@extends('layouts.app')

@section('title', 'Import Produk dari Excel')
@section('page-title', 'Import Produk dari Excel')

@section('content')

<div class="mx-auto max-w-lg">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200/80">

        @if(session('import_result'))
            @php $result = session('import_result'); @endphp
            <div class="mb-6 rounded-xl bg-blue-50 p-4 ring-1 ring-blue-200">
                <h3 class="mb-2 font-semibold text-blue-800">Hasil Import</h3>
                <ul class="space-y-1 text-sm text-blue-700">
                    <li>SKU berhasil dibuat: <strong>{{ $result['created'] ?? 0 }}</strong></li>
                    <li>SKU sudah ada (skip): <strong>{{ $result['skipped'] ?? 0 }}</strong></li>
                    <li>Baris gagal: <strong>{{ $result['failed'] ?? 0 }}</strong></li>
                </ul>
                @if(!empty($result['errors']))
                    <details class="mt-3">
                        <summary class="cursor-pointer text-xs font-medium text-blue-700">Lihat detail error</summary>
                        <div class="mt-2 max-h-40 overflow-y-auto rounded-lg bg-white p-2 font-mono text-xs text-red-600">
                            @foreach($result['errors'] as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        @endif

        <p class="mb-6 text-sm text-gray-600">
            Upload file Excel (<code>.xlsx</code>) sesuai format <strong>MASTER BOOK TOKO</strong>.
            Import akan membaca semua sheet yang dikenali dan membuat SKU secara otomatis.
        </p>

        <form method="POST" action="{{ route('admin.items.import') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">File Excel <span class="text-red-500">*</span></label>
                <input type="file" name="excel_file" accept=".xlsx,.xls"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm shadow-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-sm file:font-medium file:text-blue-700 @error('excel_file') border-red-400 @enderror">
                @error('excel_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6 rounded-xl bg-amber-50 p-4 text-xs text-amber-700 ring-1 ring-amber-200">
                <strong>Catatan:</strong> Import bersifat additive (hanya tambah, tidak overwrite SKU yang sudah ada).
                SKU duplikat akan di-skip. Semua perubahan tercatat di audit log.
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                    Mulai Import
                </button>
                <a href="{{ route('admin.items.index') }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
