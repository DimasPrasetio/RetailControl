# MANAGEMENT PRODUCT
Last verified: 2026-03-28

## 1. Ringkasan

Modul product sudah mencakup fondasi master data produk yang lengkap:
- brand
- category (unlimited nesting, cascade deactivate)
- UoM
- item
- multi-unit product per item
- attribute definition per tenant
- category attribute set
- import Excel master produk
- pricing foundation via price list
- CRUD barcode / QR code per item (dengan soft delete dan race-condition protection)

Desain sekarang mengikuti PRD sepenuhnya:
- item wajib punya satuan dasar stok
- item bisa punya beberapa satuan tambahan dengan konversi eksplisit
- default satuan jual dan default satuan beli dipisahkan
- atribut fleksibel tetap disimpan di JSON + definisi atribut
- barcode/QR code per item dapat dikelola via UI
- tenant isolation aktif di policy, query, validation, dan create hook
- Model Observer menangani cascade dan delete protection

---

## 2. Scope yang Sudah Implemented

**Brand**
- list, create, edit, deactivate

**Category**
- list (tree view, unlimited nesting)
- create, edit
- deactivate (cascade ke semua descendant)
- delete (diblokir jika masih punya children)
- parent category **unlimited level** (bukan hanya 1 level)

**UoM**
- list, create, edit, deactivate

**Attribute Definition**
- list, create, edit, assign ke category

**Item**
- list dengan filter brand, category, status, keyword
- create, edit, detail (dengan tampilan barcode), deactivate
- import Excel
- multi-unit configuration di form item
- soft delete (dengan cascade ke barcodes via Observer)

**Barcode / QR Code (PRD §11.3)**
- list barcode per item
- tambah barcode baru (dengan pilihan satuan opsional)
- set barcode sebagai primary
- hapus barcode (otomatis promote barcode berikutnya sebagai primary)
- ditampilkan di halaman detail item

---

## 3. Model dan Tabel yang Dipakai

**brands**
- tenant_id
- name
- is_active
- soft deletes
- index per tenant: name *(plain index; uniqueness di-enforce app-level)*

**categories**
- tenant_id
- parent_id nullable (self-referential, unlimited depth)
- name
- code nullable
- is_active
- soft deletes
- index per tenant: code *(plain index; uniqueness di-enforce app-level)*

**uoms**
- tenant_id
- code
- name
- is_active
- soft deletes
- index per tenant: code *(plain index; uniqueness di-enforce app-level)*

**attribute_definitions**
- tenant_id
- key
- label
- data_type
- unit nullable
- options_json nullable
- is_required
- soft deletes
- index per tenant: key *(plain index; uniqueness di-enforce app-level)*

**category_attribute_sets**
- category_id
- attribute_definition_id
- pivot category ke attribute definition

**items**
- tenant_id
- sku_code
- name
- brand_id nullable
- category_id nullable
- base_uom_id
- selling_uom_id nullable
- purchase_uom_id nullable
- pack_qty
- tax_included
- is_stockable
- cost_price nullable
- selling_price nullable
- minimum_selling_price nullable
- is_active
- attributes_json nullable
- custom_fields_json nullable
- raw_source_json nullable
- soft deletes
- index per tenant: sku_code *(plain index; uniqueness di-enforce app-level)*

**item_units**
- tenant_id
- item_id
- uom_id
- conversion_qty
- is_base
- allow_sale
- allow_purchase
- is_default_sale
- is_default_purchase
- is_active
- unique per item: uom_id

**item_barcodes**
- tenant_id
- item_id
- item_unit_id nullable
- barcode
- is_primary
- soft deletes
- index: `(tenant_id, barcode)` *(plain index; uniqueness di-enforce app-level dengan lockForUpdate di dalam transaksi)*

> Barcode menggunakan **plain index** (bukan DB unique) karena mendukung soft delete. Uniqueness di-enforce di level aplikasi dalam `DB::transaction` dengan `SELECT ... FOR UPDATE` sebelum insert — mencegah race condition. `Rule::unique()->withoutTrashed()` **tidak dipakai** karena sudah digantikan oleh locked re-check yang lebih kuat.

**price_lists**
- tenant_id
- name
- scope `GLOBAL` or `BRANCH`
- branch_id nullable
- effective_from
- effective_to nullable
- is_active

**price_list_items**
- price_list_id
- item_id
- sell_price
- price_uom_id nullable
- notes_json nullable

---

## 4. Tenant Rules

Aturan yang sekarang enforced:
- semua master data produk bersifat tenant-aware
- super_admin wajib memilih tenant saat create brand, category, UoM, item, attribute definition, dan import
- non platform admin otomatis memakai tenant miliknya
- brand, category, UoM, attribute definition, item, dan barcode tidak bisa mereferensi data lintas tenant
- unique validation berjalan per tenant di level aplikasi

Implementasi penting:
- `app/Http/Controllers/Concerns/InteractsWithTenantContext.php`
- `app/Traits/TenantScoped.php`
- `app/Policies/BrandPolicy.php`
- `app/Policies/CategoryPolicy.php`
- `app/Policies/UomPolicy.php`
- `app/Policies/AttributeDefinitionPolicy.php`
- `app/Policies/ItemPolicy.php` (termasuk method `manageBarcodes`)

---

## 5. Observer Pattern

Observer terdaftar di `AppServiceProvider::boot()`:

**CategoryObserver** (`app/Observers/CategoryObserver.php`)
- `updating`: saat `is_active` berubah ke `false`, bulk-update semua descendant menjadi nonaktif
- `deleting`: throw `RuntimeException` jika kategori masih punya children

**ItemObserver** (`app/Observers/ItemObserver.php`)
- `deleting`: cascade soft-delete ke semua barcode item
- `restoring`: cascade restore ke barcode yang belum diklaim item lain; barcode yang sudah dipakai item lain dilewati (tidak di-restore) untuk menghindari duplikat

---

## 6. Struktur Atribut Produk

Pendekatan yang dipakai:
- definisi atribut disimpan di `attribute_definitions`
- category dihubungkan ke definisi atribut lewat `category_attribute_sets`
- nilai aktual item disimpan di `items.attributes_json`

Konsekuensi:
- kategori bisa punya atribut teknis berbeda
- form item merender field dinamis berdasarkan kategori
- struktur tetap fleksibel tanpa menambah kolom tetap setiap kali ada variasi spesifikasi

Ini selaras dengan kebutuhan PRD untuk produk heterogen dan sesuai catatan engineering PRD §22.

---

## 7. Struktur Kategori Hierarki

Pendekatan yang sekarang aktif:
- `parent_id` self-referential, tidak ada batas kedalaman
- index page merender tree rekursif via `_node.blade.php` (in-memory Collection, tidak ada N+1)
- parent dropdown di form create/edit menampilkan depth-aware flat list dengan prefix `— ` per level
- circular reference diblokir di controller sebelum save
- deactivate category akan cascade ke semua descendant (ditangani `CategoryObserver`)
- delete diblokir jika masih punya children; controller menampilkan pesan error dari Observer

---

## 8. Struktur Multi-Satuan Produk

Pendekatan yang sekarang aktif:
- `base_uom_id` = satuan dasar stok
- `selling_uom_id` = default satuan jual
- `purchase_uom_id` = default satuan beli
- `item_units` = daftar semua satuan item beserta konversi ke satuan dasar
- `pack_qty` dipertahankan sebagai compatibility field untuk konversi default satuan beli ke satuan dasar

---

## 9. Barcode / QR Code (PRD §11.3)

Pendekatan yang dipakai:
- barcode disimpan di tabel `item_barcodes` terpisah dengan soft delete
- satu item bisa punya banyak barcode
- satu barcode dapat terikat ke satuan tertentu (`item_unit_id`) atau berlaku untuk semua satuan
- barcode utama (`is_primary`) digunakan sebagai referensi scan default di POS
- barcode pertama yang ditambahkan otomatis menjadi primary
- menghapus barcode primary akan otomatis mempromosikan barcode berikutnya

Uniqueness enforcement:
- **Di dalam transaksi**: sebelum insert, dilakukan `SELECT ... FOR UPDATE` untuk re-check duplikat secara atomic
- Jika duplikat ditemukan, throw `DuplicateBarcodeException` dan controller menampilkan error ke field barcode
- Saat item dihapus (soft delete): semua barcodenya ikut soft-delete via `ItemObserver`
- Saat item di-restore: barcode yang belum diklaim item lain di-restore; yang sudah diklaim dilewati

Controller: `app/Http/Controllers/Admin/ItemBarcodeController.php`
Exception: `app/Exceptions/DuplicateBarcodeException.php`

---

## 10. Pricing Saat Ini

Harga dasar item:
- `cost_price`
- `selling_price`
- `minimum_selling_price`

Harga berbasis daftar:
- `price_lists`
- `price_list_items`
- `price_uom_id` sudah dipakai untuk menyimpan satuan harga

---

## 11. Import Excel

Controller:
- `GET  /admin/items/import`
- `POST /admin/items/import`

Service: `app/Services/Product/ExcelImportService.php`

Command: `php artisan products:import {path?} --tenant={id}`

---

## 12. HTTP Routes

```
GET    /admin/brands
GET    /admin/brands/create
POST   /admin/brands
GET    /admin/brands/{brand}/edit
PUT    /admin/brands/{brand}
PATCH  /admin/brands/{brand}/deactivate

GET    /admin/categories
GET    /admin/categories/create
POST   /admin/categories
GET    /admin/categories/{category}/edit
PUT    /admin/categories/{category}
PATCH  /admin/categories/{category}/deactivate
DELETE /admin/categories/{category}

GET    /admin/uoms
GET    /admin/uoms/create
POST   /admin/uoms
GET    /admin/uoms/{uom}/edit
PUT    /admin/uoms/{uom}
PATCH  /admin/uoms/{uom}/deactivate

GET    /admin/attribute-definitions
GET    /admin/attribute-definitions/create
POST   /admin/attribute-definitions
GET    /admin/attribute-definitions/{attribute_definition}/edit
PUT    /admin/attribute-definitions/{attribute_definition}

GET    /admin/items
GET    /admin/items/import
POST   /admin/items/import
GET    /admin/items/create
POST   /admin/items
GET    /admin/items/{item}
GET    /admin/items/{item}/edit
PUT    /admin/items/{item}
PATCH  /admin/items/{item}/deactivate

GET    /admin/items/{item}/barcodes
POST   /admin/items/{item}/barcodes
PATCH  /admin/items/{item}/barcodes/{barcode}/set-primary
DELETE /admin/items/{item}/barcodes/{barcode}
```

---

## 13. Permission Map yang Relevan

- `items.view`, `items.create`, `items.update`, `items.deactivate`, `items.import`, `items.manage_barcodes`
- `brands.view`, `brands.create`, `brands.update`, `brands.deactivate`
- `categories.view`, `categories.create`, `categories.update`, `categories.deactivate`, `categories.delete`
- `uoms.view`, `uoms.create`, `uoms.update`, `uoms.deactivate`
- `attribute_definitions.view`, `attribute_definitions.create`, `attribute_definitions.update`

Role behavior saat ini:
- **super_admin**: semua boleh
- **admin**: boleh CRUD master data produk, attribute definition, import, dan manage barcode
- **owner**: read only
- **kasir**: read only (tidak bisa manage barcode)

---

## 14. Test Coverage

Feature test yang aktif:
- T0201ItemCrudTest
- T0202BrandCategoryUomTest
- T0203AttributeDefinitionCrudTest
- T0204ItemBarcodeCrudTest

Coverage yang sudah terbukti:
- create item
- create item dengan beberapa satuan jual
- validasi default satuan jual harus berasal dari item_units
- update item
- deactivate item
- item detail
- filter item list
- create brand, category, UoM
- create dan update attribute definition
- duplicate validation
- tenant wajib dipilih oleh super_admin
- kasir tidak bisa create, update, deactivate
- admin dapat tambah barcode ke item
- barcode pertama otomatis menjadi primary
- barcode duplikat dalam tenant ditolak
- admin dapat set barcode sebagai primary
- admin dapat hapus barcode
- hapus primary barcode otomatis mempromosikan barcode berikutnya
- kasir tidak bisa manage barcode

---

## 15. Alignment dengan PRD

Sudah selaras:
- produk heterogen didukung (PRD §11.3)
- tenant isolation aktif
- atribut teknis fleksibel aktif (PRD §22 poin 1-2)
- produk punya satuan dasar stok
- produk bisa punya beberapa satuan
- harga jual dan minimum harga sudah ada
- import master produk sudah ada
- QR code / barcode per item sudah ada (PRD §11.3)
- category unlimited nesting
- cascade deactivate dan delete protection via Observer

Belum ada di modul ini:
- pricing rule engine
- multi-price per price list untuk beberapa satuan item sekaligus
- customer specific pricing
- branch specific pricing UI
- stock mutation dan availability per location di UI product

---

## 16. Kesimpulan

Modul product sekarang sudah selaras penuh dengan PRD §11.3. Fondasi atribut dinamis, tenant isolation, multi-satuan produk, category unlimited hierarchy, dan barcode/QR code sudah ada. Barcode uniqueness dijaga secara transaksional dengan `lockForUpdate`, bukan hanya validasi form biasa. Area yang sengaja masih tipis adalah pricing orchestration dan interaksi inventory yang lebih kaya, karena itu lebih tepat dimatangkan bersama modul POS, procurement, dan inventory flow berikutnya.
