# MANAGEMENT BRANCH AND WAREHOUSE
Last verified: 2026-03-28

## 1. Ringkasan

Modul branch dan warehouse sudah menjadi fondasi struktur operasional sesuai PRD:
- multi-branch per tenant
- multi-warehouse per branch
- auto-create MAIN warehouse saat branch dibuat
- auto-create root stock location saat branch dibuat
- stock location CRUD dengan unlimited hierarchy
- cascade deactivate ke seluruh descendant via Observer
- inventory ledger + stock balance foundation sudah ada

Arsitektur saat ini:
- CRUD branch, warehouse, dan stock location ada di controller biasa
- validasi memakai FormRequest atau validation langsung di controller
- akses dibatasi policy + permission + tenant/branch scope
- Model Observer menangani cascade dan delete protection
- ledger dan balance dipisahkan ke service karena akan dipakai modul transaksi berikutnya

---

## 2. Scope yang Sudah Implemented

**Branch**
- list, create, show, edit, deactivate, reactivate, delete

**Warehouse**
- list, create, show, edit, deactivate, reactivate

**Stock Location**
- list (tree mode dan search/flat mode)
- create, edit
- deactivate (cascade ke semua descendant)
- reactivate
- delete (diblokir jika masih punya children)
- parent-child hierarchy unlimited level per warehouse

**Inventory Foundation**
- `stock_locations` table
- `inventory_ledgers` table
- `stock_balances` table (materialized balance)
- `InventoryLedgerService`

---

## 3. Model dan Tabel yang Dipakai

**branches**
- tenant_id
- branch_code
- name
- address nullable
- phone nullable
- timezone
- notes nullable
- metadata_json nullable
- is_active
- soft deletes
- index per tenant: branch_code *(plain index; uniqueness di-enforce app-level)*

**warehouses**
- tenant_id
- branch_id
- warehouse_code
- name
- type `MAIN` or `SECONDARY` or `RETURNS`
- notes nullable
- metadata_json nullable
- is_active
- soft deletes
- index per tenant: warehouse_code *(plain index; uniqueness di-enforce app-level)*

**stock_locations**
- tenant_id
- branch_id nullable
- warehouse_id nullable
- parent_id nullable (self-referential, unlimited depth)
- code
- name
- system_type `enum('BRANCH','WAREHOUSE') nullable` — diisi otomatis oleh sistem, null untuk lokasi buatan user
- label `varchar(50) nullable` — label bebas yang bisa diisi user (mis. Rak, Lantai, Laci)
- metadata_json nullable
- is_active
- soft deletes
- index per tenant: code *(plain index; uniqueness di-enforce app-level)*

> `system_type` dan `label` adalah dua kolom terpisah. `system_type` hanya diisi sistem (saat branch dibuat); user tidak bisa mengubahnya. `label` adalah teks bebas opsional dari user. `isSystemLocation()` cek `system_type !== null`.

**inventory_ledgers**
- tenant_id
- branch_id nullable
- warehouse_id nullable
- stock_location_id (wajib diisi via service)
- item_id
- uom_id
- transaction_uom_id nullable
- user_id nullable
- movement_type
- qty_in
- qty_out
- transaction_qty_in nullable
- transaction_qty_out nullable
- balance_after
- source_type nullable
- source_id nullable
- reference_number nullable
- notes_json nullable
- created_at

> Ledger bersifat append-only (immutable). `balance_after` selalu diisi oleh service.

**stock_balances**
- tenant_id
- item_id
- stock_location_id (NOT NULL)
- qty
- unique: `(item_id, stock_location_id)`

> Tabel ini adalah materialized view dari ledger. Di-update atomik dalam transaksi yang sama dengan ledger insert. Query stok saat ini cukup `SELECT qty FROM stock_balances WHERE item_id=? AND stock_location_id=?` — O(1), tidak perlu scan seluruh ledger.

Interpretasi ledger:
- `uom_id` = satuan dasar stok item
- `qty_in` / `qty_out` = qty yang sudah dinormalisasi ke satuan dasar stok
- `transaction_uom_id` = satuan transaksi asli
- `transaction_qty_in` / `transaction_qty_out` = qty transaksi asli sebelum normalisasi

---

## 4. Perilaku Branch

Saat branch dibuat:
- tenant_id wajib valid
- branch_code dinormalisasi uppercase
- branch_code unique per tenant (validasi app-level)
- branch otomatis membuat warehouse MAIN dengan code `{BRANCH_CODE}-MAIN`
- branch otomatis membuat stock location root dengan code `{BRANCH_CODE}-MAIN-ROOT` dan `system_type = 'WAREHOUSE'`

Scope akses:
- super_admin bisa melihat dan membuat branch lintas tenant
- owner bisa melihat semua branch dalam tenant sendiri
- admin dan kasir hanya melihat branch yang terkait dengan branch_id mereka
- admin tidak punya permission create branch

---

## 5. Perilaku Warehouse

Saat warehouse dibuat:
- branch_id wajib valid
- tenant_id diambil dari branch
- warehouse_code dinormalisasi uppercase
- warehouse_code unique per tenant (validasi app-level)
- branch harus aktif

Rule penting:
- warehouse MAIN tidak bisa diubah menjadi tipe lain
- warehouse MAIN tidak bisa dinonaktifkan
- non global user hanya bisa create atau update warehouse pada branch sendiri

---

## 6. Perilaku Stock Location

Saat stock location dibuat:
- branch harus valid dan aktif
- warehouse harus berada pada branch yang sama
- code unique per tenant (validasi app-level)
- parent harus berasal dari warehouse yang sama
- parent dropdown menampilkan hierarki depth-aware dengan prefix `— ` per level
- circular reference diblokir di controller sebelum save

Rule penting:
- lokasi sistem (`system_type IS NOT NULL`) tidak bisa diubah manual
- lokasi sistem tidak bisa dinonaktifkan via UI
- **deactivate**: cascade otomatis ke seluruh descendant (ditangani `StockLocationObserver`)
- **delete**: diblokir dengan `RuntimeException` jika masih punya children; controller menampilkan pesan error

---

## 7. Observer Pattern

Observer terdaftar di `AppServiceProvider::boot()`:

**StockLocationObserver** (`app/Observers/StockLocationObserver.php`)
- `updating`: saat `is_active` berubah ke `false`, bulk-update semua descendant menjadi nonaktif
- `deleting`: throw `RuntimeException` jika lokasi masih punya children

---

## 8. Tenant dan Branch Isolation

Aturan yang sekarang aktif:
- branch, warehouse, stock location, ledger, dan stock balance semua punya tenant context
- warehouse dan stock location juga branch-aware
- query index memfilter tenant lebih dulu, lalu branch bila perlu
- create hook model menolak create tanpa tenant yang eksplisit atau bisa diinferensi
- fallback tenant hardcoded sudah dihilangkan

Implementasi penting:
- `app/Traits/TenantScoped.php`
- `app/Traits/BranchScoped.php`
- `app/Policies/BranchPolicy.php`
- `app/Policies/WarehousePolicy.php`
- `app/Policies/StockLocationPolicy.php`
- `app/Observers/StockLocationObserver.php`
- `app/Http/Requests/Admin/StoreBranchRequest.php`
- `app/Http/Requests/Admin/StoreWarehouseRequest.php`
- `app/Http/Controllers/Admin/StockLocationController.php`

---

## 9. Inventory Foundation

Yang sudah ada:
- tabel `stock_locations`
- tabel `inventory_ledgers`
- tabel `stock_balances` (materialized balance per item per lokasi)
- model `InventoryLedger` immutable
- model `StockBalance`
- service `InventoryLedgerService::record()`
- ledger menyimpan satuan transaksi asli sekaligus qty yang dinormalisasi ke satuan dasar stok

Rule service saat ini:
- `StockLocation` **wajib** diisi — tidak bisa catat movement tanpa lokasi yang jelas
- `qty_in` dan `qty_out` tidak boleh terisi bersamaan
- salah satu harus lebih besar dari nol
- stock location harus berada pada tenant yang sama dengan item
- `performedBy` user harus berada pada tenant yang sama dengan item bila tenant user tidak null
- `transaction_uom_id` harus terdaftar pada item
- `balance_after` dihitung dari ledger terakhir pada item dan lokasi yang sama
- `stock_balances` di-update atomik dalam transaksi DB yang sama dengan insert ledger
- ledger bersifat append-only

Context opsional dibungkus dalam `LedgerInput` DTO (`app/Services/Inventory/LedgerInput.php`):
- `sourceType`, `sourceId`, `referenceNumber`, `notes`, `performedBy`, `transactionUomId`

Yang belum ada:
- UI inventory adjustment
- transfer antar gudang
- picking, packing, in-transit stock
- reservation atau allocation stock

---

## 10. HTTP Routes

```
GET    /admin/branches
GET    /admin/branches/create
POST   /admin/branches
GET    /admin/branches/{branch}
GET    /admin/branches/{branch}/edit
PUT    /admin/branches/{branch}
PATCH  /admin/branches/{branch}/deactivate
PATCH  /admin/branches/{branch}/reactivate

GET    /admin/warehouses
GET    /admin/warehouses/create
POST   /admin/warehouses
GET    /admin/warehouses/{warehouse}
GET    /admin/warehouses/{warehouse}/edit
PUT    /admin/warehouses/{warehouse}
PATCH  /admin/warehouses/{warehouse}/deactivate
PATCH  /admin/warehouses/{warehouse}/reactivate

GET    /admin/stock-locations
GET    /admin/stock-locations/create
POST   /admin/stock-locations
GET    /admin/stock-locations/{stock_location}/edit
PUT    /admin/stock-locations/{stock_location}
PATCH  /admin/stock-locations/{stock_location}/deactivate
PATCH  /admin/stock-locations/{stock_location}/reactivate
DELETE /admin/stock-locations/{stock_location}
```

---

## 11. Permission Map yang Relevan

- `branches.view`, `branches.create`, `branches.update`, `branches.deactivate`
- `warehouses.view`, `warehouses.create`, `warehouses.update`, `warehouses.deactivate`
- `stock_locations.view`, `stock_locations.create`, `stock_locations.update`, `stock_locations.deactivate`, `stock_locations.delete`

Role behavior saat ini:
- **super_admin**: semua boleh
- **owner**: view only lintas branch tenant sendiri
- **admin**: view branch; full CRUD warehouse dan stock location dalam scope branch yang sah
- **kasir**: view only

---

## 12. Seeder dan Data Awal

Seeder yang relevan:
- TenantSeeder
- BranchSeeder

Seed default saat ini membuat:
- tenant VIONI
- branch **UTAMA** (Cabang Utama)
- satu MAIN warehouse per branch dengan code `UTAMA-MAIN`
- satu root stock location per branch dengan code `UTAMA-MAIN-ROOT`

---

## 13. Test Coverage

Feature test yang aktif:
- T0301BranchCrudTest
- T0302WarehouseCrudTest
- T0303StockLocationCrudTest
- TenantAndInventoryFoundationTest

Coverage yang sudah terbukti:
- super_admin create branch
- create branch otomatis membuat MAIN warehouse
- admin hanya melihat branch sendiri
- admin tidak bisa create branch
- admin bisa create warehouse di branch sendiri
- admin tidak bisa create warehouse di branch lain
- admin bisa create dan deactivate stock location custom
- lokasi sistem tidak bisa dinonaktifkan
- kasir hanya bisa view warehouse dan stock location
- inventory ledger per tenant dan per location
- inventory ledger immutable
- inventory ledger menyimpan transaction unit dan normalized base qty

---

## 14. Alignment dengan PRD

Sudah selaras:
- multi-branch per tenant
- multi-warehouse per branch
- fondasi lokasi stok dengan unlimited hierarchy
- fondasi inventory ledger
- traceability satuan transaksi pada ledger
- stock balance materialized (query stok O(1))
- isolasi tenant dari awal

Belum ada di modul ini:
- stock transfer
- stock opname
- replenishment
- warehouse operation workflow
- reservation, pick list, packing list

---

## 15. Kesimpulan

Modul branch dan warehouse sekarang sudah cukup kuat sebagai fondasi procurement, POS, delivery, dan inventory flow berikutnya. Struktur multi-branch, multi-warehouse, stock location hierarchy unlimited level, ledger yang unit-aware, dan stock balance materialized membuat fondasi ini siap dipakai transaksi nyata tanpa redesign ulang di lapisan dasar.
