@extends('layouts.app')

@section('title', 'Edit Brand')
@section('page-title', 'Edit Brand')

@section('content')

    <div class="mx-auto max-w-lg">
        <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
            <form method="POST" action="{{ route('admin.brands.update', $brand) }}">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Brand <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.brands.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-900">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection