@php
    $isEdit = isset($branch);
@endphp

<div class="space-y-4 px-6 py-5">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Profil Cabang</p>

    @if(isset($tenants))
        <div>
            <label for="tenant_id" class="mb-1.5 block text-sm font-medium text-gray-700">Tenant</label>
            <select id="tenant_id"
                    name="tenant_id"
                    required
                    {{ $isEdit ? 'disabled' : '' }}
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('tenant_id') border-red-400 bg-red-50 @enderror">
                <option value="">- Pilih tenant -</option>
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $branch->tenant_id ?? null) == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }}
                    </option>
                @endforeach
            </select>
            @if($isEdit)
                <input type="hidden" name="tenant_id" value="{{ $branch->tenant_id }}">
                <p class="mt-1.5 text-xs text-gray-500">Tenant dikunci agar relasi user, gudang, dan lokasi tetap konsisten.</p>
            @endif
            @error('tenant_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="branch_code" class="mb-1.5 block text-sm font-medium text-gray-700">Kode Cabang</label>
            <input type="text"
                   id="branch_code"
                   name="branch_code"
                   value="{{ old('branch_code', $branch->branch_code ?? '') }}"
                   {{ $isEdit ? 'readonly' : 'required' }}
                   maxlength="20"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('branch_code') border-red-400 bg-red-50 @enderror"
                   placeholder="JKT">
            @error('branch_code')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Cabang</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name', $branch->name ?? '') }}"
                   required
                   maxlength="100"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('name') border-red-400 bg-red-50 @enderror"
                   placeholder="Cabang Jakarta">
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700">Telepon</label>
            <input type="text"
                   id="phone"
                   name="phone"
                   value="{{ old('phone', $branch->phone ?? '') }}"
                   maxlength="20"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('phone') border-red-400 bg-red-50 @enderror"
                   placeholder="021-555-1234">
            @error('phone')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="timezone" class="mb-1.5 block text-sm font-medium text-gray-700">Timezone</label>
            <input type="text"
                   id="timezone"
                   name="timezone"
                   value="{{ old('timezone', $branch->timezone ?? 'Asia/Jakarta') }}"
                   maxlength="50"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('timezone') border-red-400 bg-red-50 @enderror"
                   placeholder="Asia/Jakarta">
            @error('timezone')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">Alamat</label>
        <textarea id="address"
                  name="address"
                  rows="3"
                  class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('address') border-red-400 bg-red-50 @enderror"
                  placeholder="Alamat lengkap cabang">{{ old('address', $branch->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700">Catatan</label>
        <textarea id="notes"
                  name="notes"
                  rows="3"
                  class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('notes') border-red-400 bg-red-50 @enderror"
                  placeholder="Catatan operasional cabang">{{ old('notes', $branch->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
        <div class="flex h-5 items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-400/30">
        </div>
        <div>
            <label for="is_active" class="cursor-pointer text-sm font-medium text-gray-700">Cabang aktif</label>
            <p class="text-xs text-gray-500">Cabang aktif dapat dipilih untuk user dan gudang.</p>
        </div>
    </div>
</div>
