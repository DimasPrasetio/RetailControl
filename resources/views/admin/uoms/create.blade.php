@extends('layouts.app')

@section('title', 'Tambah Satuan')
@section('page-title', 'Tambah Satuan (UoM)')

@section('content')
<div class="mx-auto max-w-lg">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200/80">
        <form method="POST" action="{{ route('admin.uoms.store') }}">
            @csrf
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="PCS, BATANG, BOX, dll"
                       class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm uppercase shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('code') border-red-400 @enderror">
                @error('code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="mb-6">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Pieces, Batang, Box / Dus, dll"
                       class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">Simpan</button>
                <a href="{{ route('admin.uoms.index') }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
