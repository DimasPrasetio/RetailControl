@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk (SKU)')

@section('content')

    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl border border-slate-300 bg-white p-6 shadow-xl shadow-indigo-500/10">
            <form method="POST" action="{{ route('admin.items.store') }}">
                @csrf

                @if(auth()->user()->isPlatformAdmin())
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <label class="mb-1.5 block text-sm font-medium text-amber-900">Perusahaan <span class="text-red-500">*</span></label>
                        <select name="tenant_id"
                            onchange="window.location='{{ route('admin.items.create') }}?tenant_id=' + this.value"
                            class="tom-select-init w-full rounded-xl border border-amber-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('tenant_id') border-red-400 @enderror">
                            <option value="">- Pilih perusahaan untuk memuat referensi master data -</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id', $selectedTenantId) == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                        @error('tenant_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        @if(! old('tenant_id', $selectedTenantId))
                            <p class="mt-2 text-xs text-amber-800">Pilih perusahaan terlebih dahulu agar brand, kategori, dan satuan hanya berasal dari perusahaan yang benar.</p>
                        @endif
                    </div>
                @endif

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Identitas Produk</h3>

                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">SKU Code <span class="text-red-500">*</span></label>
                        <input type="text" name="sku_code" value="{{ old('sku_code') }}"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm uppercase shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('sku_code') border-red-400 @enderror">
                        @error('sku_code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                        <div class="flex items-center gap-2 pt-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-blue-600">
                            <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Brand</label>
                        <select name="brand_id" class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                            <option value="">- Tidak ada -</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category_id" id="category_id" class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                            <option value="">- Tidak ada -</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Unit & Kemasan</h3>

                <div class="mb-4 grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan Dasar Stok <span class="text-red-500">*</span></label>
                        <select name="base_uom_id" id="base_uom_id" class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 @error('base_uom_id') border-red-400 @enderror">
                            <option value="">- Pilih -</option>
                            @foreach($uoms as $uom)
                                <option value="{{ $uom->id }}" {{ old('base_uom_id') == $uom->id ? 'selected' : '' }}>{{ $uom->code }}</option>
                            @endforeach
                        </select>
                        @error('base_uom_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-gray-500">Semua pergerakan stok dicatat dalam satuan ini.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Default Satuan Jual</label>
                        <select name="selling_uom_id" id="selling_uom_id" class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 @error('selling_uom_id') border-red-400 @enderror">
                            <option value="">- Ikuti satuan dasar stok -</option>
                            @foreach($uoms as $uom)
                                <option value="{{ $uom->id }}" {{ old('selling_uom_id') == $uom->id ? 'selected' : '' }}>{{ $uom->code }}</option>
                            @endforeach
                        </select>
                        @error('selling_uom_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-gray-500">Harus termasuk satuan dasar atau daftar satuan tambahan di bawah.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Default Satuan Beli</label>
                        <select name="purchase_uom_id" id="purchase_uom_id" class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                            <option value="">- Tidak diatur -</option>
                            @foreach($uoms as $uom)
                                <option value="{{ $uom->id }}" {{ old('purchase_uom_id') == $uom->id ? 'selected' : '' }}>{{ $uom->code }}</option>
                            @endforeach
                        </select>
                        @error('purchase_uom_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-gray-500">Konversi satuan beli mengikuti daftar satuan tambahan.</p>
                    </div>
                </div>

                <div class="mb-6 rounded-2xl border border-dashed border-gray-200 bg-gray-50/70 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">Satuan Tambahan</h4>
                            <p class="text-xs text-gray-500">Tambahkan satuan jual atau beli lain beserta konversinya ke satuan dasar stok.</p>
                        </div>
                        <button type="button" onclick="addUnitRow()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-blue-400 hover:text-blue-600">
                            Tambah Satuan
                        </button>
                    </div>
                    @error('item_units')<p class="mb-2 text-xs text-red-500">{{ $message }}</p>@enderror
                    <div id="item-unit-rows" class="space-y-3"></div>
                </div>

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Harga & Stok</h3>
                <p class="mb-3 text-xs text-gray-500">Harga modal diinput per default satuan beli. Harga jual &amp; minimum per default satuan jual.</p>

                <div class="mb-4 grid grid-cols-3 gap-4">
                    <div>
                        <label id="cost-price-label" for="cost_price" class="mb-1.5 block text-sm font-medium text-gray-700">Harga Modal</label>
                        <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price') }}" min="0" step="0.01"
                            oninput="updatePriceLabels()"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                        <p id="cost-price-hint" class="mt-1 min-h-[1rem] text-xs text-blue-500"></p>
                    </div>
                    <div>
                        <label id="selling-price-label" for="selling_price" class="mb-1.5 block text-sm font-medium text-gray-700">Harga Jual Normal</label>
                        <input type="number" id="selling_price" name="selling_price" value="{{ old('selling_price') }}" min="0" step="0.01"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                    </div>
                    <div>
                        <label id="minimum-price-label" for="minimum_selling_price" class="mb-1.5 block text-sm font-medium text-gray-700">Harga Minimum</label>
                        <input type="number" id="minimum_selling_price" name="minimum_selling_price" value="{{ old('minimum_selling_price') }}" min="0" step="0.01"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                    </div>
                </div>

                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="is_stockable" id="is_stockable" value="1" {{ old('is_stockable', 1) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="is_stockable" class="text-sm text-gray-700">Produk ini mempengaruhi stok</label>
                </div>

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Pajak</h3>
                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="tax_included" id="tax_included" value="1" {{ old('tax_included', 1) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="tax_included" class="text-sm text-gray-700">Harga sudah termasuk PPN</label>
                </div>

                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Spesifikasi Produk</h3>
                <p class="mb-3 text-xs text-gray-500">Field spesifikasi akan mengikuti kategori yang dipilih.</p>
                <div id="category-attributes" class="mb-6 space-y-4"></div>

                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Informasi Tambahan <span class="text-xs font-normal normal-case text-gray-500">(opsional)</span></h3>
                <p class="mb-3 text-xs text-gray-500">Detail lain di luar spesifikasi utama, misalnya warna atau catatan khusus.</p>
                <div id="custom-rows" class="mb-6 space-y-2">
                    <div class="flex items-center gap-2 custom-row">
                        <input type="text" name="custom_keys[]" placeholder="Informasi"
                            class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                        <input type="text" name="custom_values[]" placeholder="Keterangan"
                            class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                        <button type="button" onclick="this.closest('.custom-row').remove()"
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addCustomRow()"
                    class="mb-6 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-blue-400 hover:text-blue-600">
                    Tambah Detail Tambahan
                </button>

                <div class="flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan
                    </button>
                    <a href="{{ route('admin.items.index') }}"
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

    <script>
        const categoryAttributes = @json($categoryAttributes);
        const initialAttributes = @json(old('attributes', []));
        const unitRowsSeed = @json(old('item_units', $itemUnitRows ?? []));
        const uomOptions = @json($uoms->map(fn ($uom) => ['id' => $uom->id, 'code' => $uom->code])->values());
        let unitRowIndex = 0;

        function addCustomRow() {
            const container = document.getElementById('custom-rows');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 custom-row';
            row.innerHTML = `
                <input type="text" name="custom_keys[]" placeholder="Informasi"
                    class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                <input type="text" name="custom_values[]" placeholder="Keterangan"
                    class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                <button type="button" onclick="this.closest('.custom-row').remove()"
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>`;
            container.appendChild(row);
        }

        function renderCategoryAttributes() {
            const categorySelect = document.getElementById('category_id');
            const container = document.getElementById('category-attributes');
            const defs = categoryAttributes[categorySelect.value] || [];

            container.innerHTML = '';

            if (!defs.length) {
                container.innerHTML = '<p class="rounded-xl border border-dashed border-gray-200 px-4 py-3 text-xs text-gray-500">Kategori ini belum memiliki definisi atribut.</p>';
                return;
            }

            defs.forEach(def => {
                const value = initialAttributes[def.key] ?? '';
                const wrapper = document.createElement('div');
                wrapper.className = 'grid gap-2';

                let inputHtml = '';
                if (def.data_type === 'select') {
                    const options = (def.options || [])
                        .map(option => `<option value="${option}" ${value === option ? 'selected' : ''}>${option}</option>`)
                        .join('');
                    inputHtml = `<select name="attributes[${def.key}]" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400"><option value="">- Pilih -</option>${options}</select>`;
                } else if (def.data_type === 'boolean') {
                    inputHtml = `<label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="attributes[${def.key}]" value="1" ${value ? 'checked' : ''} class="h-4 w-4 rounded border-gray-300 text-blue-600">Ya</label>`;
                } else {
                    const type = def.data_type === 'number' ? 'number' : 'text';
                    const step = def.data_type === 'number' ? 'step="0.01"' : '';
                    inputHtml = `<input type="${type}" ${step} name="attributes[${def.key}]" value="${value ?? ''}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">`;
                }

                wrapper.innerHTML = `
                    <label class="text-sm font-medium text-gray-700">${def.label}${def.unit ? ` <span class="text-xs text-gray-500">(${def.unit})</span>` : ''}</label>
                    ${inputHtml}
                `;
                container.appendChild(wrapper);
            });
        }

        function renderUnitRow(row = {}, index = unitRowIndex++) {
            const container = document.getElementById('item-unit-rows');
            const wrapper = document.createElement('div');
            wrapper.className = 'unit-row grid gap-3 rounded-xl border border-gray-200 bg-white p-3 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_auto_auto_auto]';

            const selectedUomId = row.uom_id ?? '';
            const optionsHtml = ['<option value="">- Pilih satuan -</option>']
                .concat(uomOptions.map(option => `<option value="${option.id}" ${String(selectedUomId) === String(option.id) ? 'selected' : ''}>${option.code}</option>`))
                .join('');

            wrapper.innerHTML = `
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500">Satuan</label>
                    <select name="item_units[${index}][uom_id]" onchange="updatePriceLabels()" class="tom-select-init w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400">
                        ${optionsHtml}
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500">Konversi ke Dasar</label>
                    <input type="number" name="item_units[${index}][conversion_qty]" value="${row.conversion_qty ?? ''}" min="0.0001" step="0.0001"
                        oninput="updatePriceLabels()"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400">
                </div>
                <label class="flex items-center gap-2 self-end rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="item_units[${index}][allow_sale]" value="1" ${(row.allow_sale ?? false) ? 'checked' : ''} class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    Jual
                </label>
                <label class="flex items-center gap-2 self-end rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="item_units[${index}][allow_purchase]" value="1" ${(row.allow_purchase ?? false) ? 'checked' : ''} class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    Beli
                </label>
                <button type="button" onclick="this.closest('.unit-row').remove(); updatePriceLabels()"
                    class="self-end rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                    Hapus
                </button>
            `;

            container.appendChild(wrapper);

            // Initialize CustomSelect on the new select (guards against double-init via _cs flag)
            const newSelect = wrapper.querySelector('select[name*="[uom_id]"]');
            if (newSelect && !newSelect._cs) {
                new CustomSelect(newSelect);
            }
        }

        function addUnitRow() {
            renderUnitRow();
        }

        function updatePriceLabels() {
            const baseUomId = document.getElementById('base_uom_id')?.value ?? '';
            const sellingUomId = document.getElementById('selling_uom_id')?.value ?? '';
            const purchaseUomId = document.getElementById('purchase_uom_id')?.value ?? '';

            // Build conversion map: uomId -> { code, conversion }
            const convMap = {};
            if (baseUomId) {
                const baseCode = uomOptions.find(u => String(u.id) === baseUomId)?.code ?? '';
                convMap[baseUomId] = { code: baseCode, conversion: 1 };
            }
            document.querySelectorAll('.unit-row').forEach(row => {
                const sel = row.querySelector('select[name*="[uom_id]"]');
                const inp = row.querySelector('input[name*="[conversion_qty]"]');
                if (!sel?.value) return;
                const code = uomOptions.find(u => String(u.id) === sel.value)?.code ?? '';
                convMap[sel.value] = { code, conversion: parseFloat(inp?.value) || 1 };
            });

            const baseCode = convMap[baseUomId]?.code ?? '';

            // Resolve purchase unit info (fallback to base if not set)
            const purchaseInfo = convMap[purchaseUomId] ?? convMap[baseUomId] ?? { code: baseCode, conversion: 1 };

            // Resolve selling unit info (fallback to base if not set)
            const effectiveSellingId = sellingUomId || baseUomId;
            const sellingInfo = convMap[effectiveSellingId] ?? { code: baseCode, conversion: 1 };

            // Update Harga Modal label
            const costLabel = document.getElementById('cost-price-label');
            if (costLabel) {
                costLabel.textContent = purchaseInfo.code ? `Harga Modal per ${purchaseInfo.code}` : 'Harga Modal';
            }

            // Update Harga Modal hint: show per-base equivalent when conversion > 1
            const costHint = document.getElementById('cost-price-hint');
            if (costHint) {
                if (purchaseInfo.conversion > 1) {
                    const val = parseFloat(document.getElementById('cost_price')?.value);
                    if (!isNaN(val) && val > 0) {
                        const perBase = val / purchaseInfo.conversion;
                        costHint.textContent = `≈ Rp ${new Intl.NumberFormat('id-ID').format(Math.round(perBase))} / ${baseCode}`;
                    } else {
                        costHint.textContent = `Masukkan harga beli per ${purchaseInfo.code}. Sistem konversi ke per ${baseCode}.`;
                    }
                } else {
                    costHint.textContent = '';
                }
            }

            // Update Harga Jual Normal label
            const sellLabel = document.getElementById('selling-price-label');
            if (sellLabel) {
                sellLabel.textContent = sellingInfo.code ? `Harga Jual Normal per ${sellingInfo.code}` : 'Harga Jual Normal';
            }

            // Update Harga Minimum label
            const minLabel = document.getElementById('minimum-price-label');
            if (minLabel) {
                minLabel.textContent = sellingInfo.code ? `Harga Minimum per ${sellingInfo.code}` : 'Harga Minimum';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            if (categorySelect) {
                renderCategoryAttributes();
                categorySelect.addEventListener('change', renderCategoryAttributes);
            }

            if (unitRowsSeed.length) {
                unitRowsSeed.forEach(row => renderUnitRow(row));
            }

            ['base_uom_id', 'selling_uom_id', 'purchase_uom_id'].forEach(id => {
                document.getElementById(id)?.addEventListener('change', updatePriceLabels);
            });

            updatePriceLabels();
        });
    </script>

@endsection
