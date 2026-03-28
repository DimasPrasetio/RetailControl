# ARCHITECTURE UI — RetailControl

> Dokumen ini mendeskripsikan **business flow**, **hirarki halaman**, **arsitektur navigasi**, dan **logika proses bisnis** dari sisi UI/UX sistem RetailControl. Bukan panduan desain visual, melainkan panduan alur kerja dan interaksi pengguna.

---

## DAFTAR ISI

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Hirarki Pengguna & Hak Akses](#2-hirarki-pengguna--hak-akses)
3. [Alur Autentikasi](#3-alur-autentikasi)
4. [Peta Navigasi (Sitemap)](#4-peta-navigasi-sitemap)
5. [Dashboard](#5-dashboard)
6. [Modul Administrasi — Manajemen User](#6-modul-administrasi--manajemen-user)
7. [Modul Administrasi — Audit Log](#7-modul-administrasi--audit-log)
8. [Modul Struktur Operasional — Cabang](#8-modul-struktur-operasional--cabang)
9. [Modul Struktur Operasional — Gudang](#9-modul-struktur-operasional--gudang)
10. [Modul Struktur Operasional — Lokasi Stok](#10-modul-struktur-operasional--lokasi-stok)
11. [Modul Master Produk — Brand](#11-modul-master-produk--brand)
12. [Modul Master Produk — Kategori](#12-modul-master-produk--kategori)
13. [Modul Master Produk — Satuan (UoM)](#13-modul-master-produk--satuan-uom)
14. [Modul Master Produk — Atribut Produk](#14-modul-master-produk--atribut-produk)
15. [Modul Master Produk — Produk (SKU/Item)](#15-modul-master-produk--produk-skuitem)
16. [Manajemen Barcode](#16-manajemen-barcode)
17. [Import Produk via Excel](#17-import-produk-via-excel)
18. [Alur Data Lintas Modul (Cross-Module Flows)](#18-alur-data-lintas-modul-cross-module-flows)
19. [Hirarki Data & Relasi Model](#19-hirarki-data--relasi-model)
20. [Multi-Tenancy & Isolasi Data](#20-multi-tenancy--isolasi-data)
21. [Pola Konsistensi UI](#21-pola-konsistensi-ui)
22. [Modul Yang Akan Datang](#22-modul-yang-akan-datang)

---

## 1. Gambaran Umum Sistem

**RetailControl** adalah sistem manajemen ritel multi-tenant berbasis web yang dibangun dengan Laravel 11. Sistem ini dirancang untuk mengelola operasional bisnis ritel secara terpusat, mulai dari struktur organisasi, master data produk, hingga transaksi dan inventori.

### Filosofi Arsitektur

```
Platform (RetailControl)
  └── Tenant (Perusahaan/Brand Ritel)
        └── Branch (Cabang Toko/Kantor)
              ├── Warehouse (Gudang)
              │     └── Stock Location (Lokasi Penyimpanan)
              ├── User (Staff/Manager)
              └── Inventory (Stok Barang)
```

Setiap entitas data terikat pada **Tenant** dan kebanyakan juga pada **Branch**. Sistem ini memastikan data antar-tenant tidak bocor satu sama lain (data isolation).

### Stack Teknologi
- **Backend:** Laravel 11 (PHP)
- **Frontend:** Blade Templates + Tailwind CSS + Alpine.js
- **Database:** MySQL/PostgreSQL
- **Build Tool:** Vite

---

## 2. Hirarki Pengguna & Hak Akses

### 2.1 Struktur Role

```
SuperAdmin (Platform)
    ↓ akses penuh ke semua tenant
Owner (Tenant Global)
    ↓ read-only dalam 1 tenant, semua cabang
Admin (Tenant Global)
    ↓ akses kelola data dalam 1 tenant, semua cabang
Kasir (Cabang Tertentu)
    ↓ akses terbatas 1 cabang (operasional kasir)
```

### 2.2 Apa yang Dilihat Tiap Role

| Fitur UI | SuperAdmin | Owner | Admin | Kasir |
|---|---|---|---|---|
| Pilih Tenant di form | ✅ | ❌ (auto) | ❌ (auto) | ❌ (auto) |
| Pilih Cabang di form | ✅ | ✅ | ✅ | ❌ (auto) |
| Filter Tenant di list | ✅ | ❌ | ❌ | ❌ |
| Filter Cabang di list | ✅ | ✅ | ✅ | ❌ |
| Lihat semua tenant | ✅ | ❌ | ❌ | ❌ |
| Kelola user semua cabang | ✅ | ❌ | ✅ (deactivate only) | ❌ |

### 2.3 Sistem Permission

Setiap aksi dalam sistem dijaga oleh **permission slug** yang dilekatkan ke role. UI menyembunyikan menu/tombol yang tidak diizinkan.

**Format permission slug:** `{resource}.{action}`

```
Contoh:
  items.view        → Melihat daftar produk
  items.create      → Membuat produk baru
  items.update      → Mengedit produk
  items.deactivate  → Menonaktifkan produk
  items.import      → Import produk via Excel
  items.manage_barcodes → Kelola barcode produk
```

### 2.4 Aturan Bisnis Hak Akses

- User **tidak bisa menonaktifkan akun sendiri**
- User **tidak bisa mengubah akun SuperAdmin** kecuali SuperAdmin itu sendiri
- Jika role diset ke **SuperAdmin**, sistem otomatis mengosongkan `tenant_id` dan `branch_id`
- Manager hanya bisa kelola user di **cabangnya sendiri**

---

## 3. Alur Autentikasi

### 3.1 Login Flow

```
[Halaman /login]
    │
    ├── Input: username ATAU email + password + remember_me
    │
    ▼
[POST /login] → LoginService
    │
    ├── Cek: user exists? (by username or email)
    ├── Cek: password match?
    ├── Cek: is_active = true?
    │
    ├── GAGAL → Kembali ke form dengan error message
    │           (throttled: max percobaan terbatas)
    │
    └── BERHASIL → Redirect ke /dashboard (atau intended URL)
```

### 3.2 Session & Middleware Guard

Setiap request ke halaman admin melewati tiga lapisan guard:

```
Request
  → [auth]          — Apakah sudah login?
  → [auth.session]  — Apakah session masih valid?
  → [active]        — Apakah akun user masih aktif?
  → [permission:*]  — Apakah punya izin untuk halaman ini?
  → Controller
```

Jika **akun dinonaktifkan** saat sedang login, middleware `active` akan memblokir akses pada request berikutnya.

### 3.3 Logout Flow

```
[Klik tombol Logout]
    │
    ▼
[POST /logout]
    │
    └── Invalidate session → Redirect ke /login
```

---

## 4. Peta Navigasi (Sitemap)

### 4.1 Struktur Navigasi Sidebar

```
[Sidebar]
│
├── 🏠 Dashboard → /dashboard
│
├── ── ADMINISTRASI ──
│   ├── 👥 Manajemen User       → /admin/users          [perm: users.view]
│   └── 📋 Audit Log            → /admin/audit-logs      [perm: audit_logs.view]
│
├── ── STRUKTUR OPERASIONAL ──
│   ├── 🏢 Cabang               → /admin/branches        [perm: branches.view]
│   ├── 🏭 Gudang               → /admin/warehouses      [perm: warehouses.view]
│   └── 📍 Lokasi Stok          → /admin/stock-locations [perm: stock_locations.view]
│
├── ── MASTER PRODUK ──
│   ├── 📦 Produk (SKU)         → /admin/items           [perm: items.view]
│   ├── 🏷️ Brand                → /admin/brands          [perm: brands.view]
│   ├── 🗂️ Kategori             → /admin/categories      [perm: categories.view]
│   ├── ⚖️ Satuan (UoM)         → /admin/uoms            [perm: uoms.view]
│   └── 🔧 Atribut Produk       → /admin/attribute-defs  [perm: attribute_definitions.view]
│
└── ── OPERASIONAL (SEGERA) ──
    ├── 🛒 Transaksi POS        [disabled - Module 06]
    ├── 📊 Stok                 [disabled - Module 05]
    ├── 💳 Piutang (AR)         [disabled - Module 08]
    └── 🛍️ Pembelian            [disabled - Module 09]
```

Menu yang tidak diizinkan oleh permission **tidak ditampilkan** di sidebar.

### 4.2 Peta Lengkap Semua Halaman

```
/login                                    Login
/dashboard                                Dashboard

/admin/users                              Daftar User
/admin/users/create                       Tambah User
/admin/users/{id}/edit                    Edit User

/admin/audit-logs                         Daftar Audit Log

/admin/branches                           Daftar Cabang
/admin/branches/create                    Tambah Cabang
/admin/branches/{id}                      Detail Cabang
/admin/branches/{id}/edit                 Edit Cabang

/admin/warehouses                         Daftar Gudang
/admin/warehouses/create                  Tambah Gudang
/admin/warehouses/{id}                    Detail Gudang
/admin/warehouses/{id}/edit               Edit Gudang

/admin/stock-locations                    Daftar Lokasi Stok
/admin/stock-locations/create             Tambah Lokasi Stok
/admin/stock-locations/{id}/edit          Edit Lokasi Stok

/admin/brands                             Daftar Brand
/admin/brands/create                      Tambah Brand
/admin/brands/{id}/edit                   Edit Brand

/admin/categories                         Daftar Kategori
/admin/categories/create                  Tambah Kategori
/admin/categories/{id}/edit               Edit Kategori

/admin/uoms                               Daftar Satuan
/admin/uoms/create                        Tambah Satuan
/admin/uoms/{id}/edit                     Edit Satuan

/admin/attribute-definitions              Daftar Atribut Produk
/admin/attribute-definitions/create       Tambah Atribut Produk
/admin/attribute-definitions/{id}/edit    Edit Atribut Produk

/admin/items                              Daftar Produk (SKU)
/admin/items/create                       Tambah Produk
/admin/items/import                       Import Produk (Excel)
/admin/items/{id}                         Detail Produk
/admin/items/{id}/edit                    Edit Produk
/admin/items/{id}/barcodes                Kelola Barcode Produk
```

---

## 5. Dashboard

### 5.1 Tujuan Halaman

Halaman pertama setelah login. Memberikan gambaran singkat kondisi sistem dan navigasi cepat ke modul-modul utama.

### 5.2 Konten Dashboard

```
[Dashboard]
│
├── STATISTIK CEPAT (3 kartu)
│   ├── Total User     → jumlah semua user dalam tenant
│   ├── User Aktif     → jumlah user is_active=true
│   └── Audit Hari Ini → jumlah log aktivitas hari ini
│
├── AKSES CEPAT (Quick Links)
│   ├── Manajemen User
│   ├── Audit Log
│   ├── Master Produk
│   ├── Cabang
│   └── Gudang
│
└── MODUL MENDATANG (7 kartu "Soon")
    ├── Modul 04: Harga & Diskon
    ├── Modul 05: Inventori
    ├── Modul 06: POS Transaksi
    ├── Modul 07: Delivery Order
    ├── Modul 08: Piutang (AR)
    ├── Modul 09: Pembelian
    └── Modul 10: Akuntansi
```

### 5.3 Scoping Data Dashboard

Data statistik **selalu disaring per tenant** pengguna yang sedang login. SuperAdmin melihat agregat seluruh platform.

---

## 6. Modul Administrasi — Manajemen User

### 6.1 Deskripsi Bisnis

Modul ini digunakan untuk mengelola akun pengguna yang dapat mengakses sistem. Setiap user terikat pada tenant dan/atau cabang tertentu, dan memiliki satu role yang menentukan hak aksesnya.

### 6.2 Halaman Index — Daftar User

**Tujuan:** Melihat, mencari, dan memfilter seluruh user dalam scope akses.

```
[Daftar User]
│
├── FILTER BAR
│   ├── Search        → Cari berdasarkan nama atau username
│   ├── Filter Role   → Dropdown semua role
│   ├── Filter Status → Aktif / Tidak Aktif / Semua
│   ├── Filter Tenant → [hanya SuperAdmin] Dropdown tenant
│   └── Filter Cabang → [hanya global role] Dropdown cabang
│
├── TABEL HASIL
│   ├── Kolom: Nama, Username, Email, Role, Cabang, Status, Aksi
│   └── Pagination 10 per halaman
│
└── TOMBOL AKSI PER BARIS
    ├── [Edit]        → /admin/users/{id}/edit      [perm: users.update]
    └── [Nonaktifkan] → PATCH /admin/users/{id}/deactivate [perm: users.deactivate]
```

**Aturan Scoping List:**
- Non-global user hanya melihat user di cabangnya sendiri
- Tenant admin melihat semua user dalam tenant
- SuperAdmin melihat semua user di semua tenant

### 6.3 Halaman Create/Edit User

**Tujuan:** Membuat atau mengubah akun pengguna.

```
[Form User]
│
├── Perusahaan (Tenant)  [hanya tampil untuk SuperAdmin]
│   └── Dropdown semua tenant aktif
│
├── Cabang
│   └── Dropdown cabang (difilter berdasarkan tenant yang dipilih)
│       Kosong = tidak terikat cabang tertentu
│
├── Nama Lengkap        [required]
├── Username            [required, unique per tenant]
├── Email               [optional, unique per tenant]
├── Password            [required saat create; kosong = tidak berubah saat edit]
├── Role                [required, dropdown]
└── Status Aktif        [checkbox, default: centang]
```

**Logika Khusus:**
- Saat role **SuperAdmin** dipilih → field Tenant & Cabang otomatis dikosongkan dan disembunyikan
- Password tidak ditampilkan ulang saat edit (blank = tidak ubah password)

### 6.4 Alur Bisnis User

```
CREATE USER
  SuperAdmin memilih tenant
      ↓
  Isi nama, username, email, password
      ↓
  Pilih role
      ↓
  Jika bukan SuperAdmin: pilih cabang (opsional)
      ↓
  Submit → user dibuat → redirect ke list ✅

EDIT USER
  Ubah field apapun kecuali tenant
      ↓
  Jika ganti role ke SuperAdmin: tenant & cabang dikosongkan
      ↓
  Submit → update → redirect ke list ✅

NONAKTIFKAN USER
  Klik [Nonaktifkan] di list
      ↓
  is_active = false
      ↓
  User tidak bisa login lagi
      ↓
  Audit log tercatat ✅
```

---

## 7. Modul Administrasi — Audit Log

### 7.1 Deskripsi Bisnis

Halaman read-only untuk menelusuri riwayat semua perubahan data penting dalam sistem. Berguna untuk akuntabilitas dan investigasi masalah.

### 7.2 Halaman Index — Daftar Audit Log

```
[Daftar Audit Log]
│
├── FILTER BAR
│   ├── Tipe Data (Auditable Type)
│   │   → User, Role, Item, Kategori, Brand, Satuan, Cabang, Gudang
│   ├── User (siapa yang melakukan perubahan)
│   ├── Aksi → dibuat / diubah / dihapus
│   └── Rentang Tanggal (date_from → date_to)
│
└── TABEL HASIL
    ├── Kolom: User, Aksi, Tipe Model, Model ID, Ringkasan Perubahan, Waktu
    └── Pagination 10 per halaman, urut terbaru dulu
```

**Catatan Penting:**
- Audit log bersifat **append-only** — tidak bisa diubah atau dihapus
- Data yang diaudit: User, Branch, Warehouse, Item, Category, Brand, UOM, StockLocation, AttributeDefinition
- Non-SuperAdmin hanya melihat log milik tenantnya

---

## 8. Modul Struktur Operasional — Cabang

### 8.1 Deskripsi Bisnis

Cabang adalah unit operasional terkecil dari perusahaan. Setiap cabang memiliki gudang, lokasi stok, dan user sendiri. Saat cabang dibuat, sistem secara otomatis membuatkan struktur awal (gudang utama + lokasi stok).

### 8.2 Halaman Index — Daftar Cabang

```
[Daftar Cabang]
│
├── FILTER BAR
│   ├── Search        → Nama atau kode cabang
│   ├── Filter Status → Aktif / Tidak Aktif / Semua
│   └── Filter Tenant → [hanya SuperAdmin]
│
├── TABEL HASIL
│   ├── Kolom: Kode, Nama, Alamat, Telepon, Jml User, Jml Gudang, Status, Aksi
│   └── Pagination 10 per halaman
│
└── TOMBOL AKSI PER BARIS
    ├── [Edit]        → /admin/branches/{id}/edit
    ├── [Nonaktifkan] → PATCH /admin/branches/{id}/deactivate
    └── [Aktifkan]    → PATCH /admin/branches/{id}/reactivate
```

### 8.3 Halaman Create Cabang

```
[Form Cabang]
│
├── Perusahaan (Tenant)  [required, hanya SuperAdmin yang bisa memilih]
├── Kode Cabang          [required, unique per tenant, max 20 karakter]
├── Nama Cabang          [required]
├── Alamat               [optional]
├── Telepon              [optional]
├── Timezone             [dropdown, default: Asia/Jakarta]
└── Catatan              [optional]
```

### 8.4 Auto-Creation Saat Cabang Dibuat

Ini adalah **alur bisnis kritis** — semua terjadi dalam satu database transaction:

```
[Submit Form Cabang]
    │
    ▼
[Database Transaction]
    │
    ├── 1. Buat record Cabang
    │
    ├── 2. Auto-buat Gudang Utama
    │   ├── Kode: "{kode_cabang}-MAIN"
    │   ├── Nama: "Gudang Utama"
    │   └── Tipe: MAIN (tidak bisa dinonaktifkan)
    │
    └── 3. Auto-buat Lokasi Stok Root
        ├── Kode: "{kode_cabang}-MAIN-ROOT"
        ├── Nama: "Lokasi Utama {nama_cabang}"
        └── Tipe: WAREHOUSE (sistem, tidak bisa diedit manual)
```

### 8.5 Halaman Detail Cabang

```
[Detail Cabang]
│
├── Informasi Cabang (semua field)
│
├── DAFTAR GUDANG milik cabang ini
│   └── Link edit ke masing-masing gudang
│
└── DAFTAR USER yang terdaftar di cabang ini
    └── Ditampilkan beserta role-nya
```

---

## 9. Modul Struktur Operasional — Gudang

### 9.1 Deskripsi Bisnis

Gudang adalah tempat fisik penyimpanan stok dalam sebuah cabang. Setiap cabang minimal memiliki satu gudang utama (MAIN) yang dibuat otomatis. Dapat ditambahkan gudang sekunder (SECONDARY) atau gudang retur (RETURN).

### 9.2 Tipe Gudang

| Tipe | Keterangan | Auto-dibuat? | Bisa Nonaktif? |
|---|---|---|---|
| MAIN | Gudang utama cabang | ✅ (saat buat cabang) | ❌ |
| SECONDARY | Gudang tambahan | ❌ (manual) | ✅ |
| RETURN | Gudang barang retur | ❌ (manual) | ✅ |

### 9.3 Form Gudang

```
[Form Gudang]
│
├── Cabang               [required, dropdown]
├── Kode Gudang          [required, unique per cabang]
├── Nama Gudang          [required]
├── Tipe Gudang          [MAIN / SECONDARY / RETURN]
└── Catatan              [optional]
```

**Aturan edit:** Tipe MAIN tidak bisa diubah setelah dibuat.

---

## 10. Modul Struktur Operasional — Lokasi Stok

### 10.1 Deskripsi Bisnis

Lokasi stok adalah struktur hierarkis penyimpanan di dalam gudang. Digunakan untuk menentukan **di mana tepatnya** suatu stok berada. Contoh: Gudang → Area A → Rak 1 → Slot A1.

### 10.2 Tipe Lokasi Stok

Sistem membedakan dua hal yang berbeda:

| Kolom | Isi | Siapa yang mengisi |
|---|---|---|
| `system_type` | `BRANCH` atau `WAREHOUSE` | Sistem saja (otomatis saat buat cabang) |
| `label` | Teks bebas (mis. Rak, Lantai, Blok) | User (opsional) |

Lokasi buatan user **tidak punya `system_type`** — hanya bisa mengisi `label` sebagai penanda bebas.

### 10.3 Hirarki Lokasi Stok (Contoh)

Hierarki tidak dibatasi levelnya (unlimited depth):

```
Cabang A
└── Gudang Utama (Tipe: MAIN)
    └── [Lokasi Utama Cabang A] ← system_type: WAREHOUSE, auto-buat, tidak bisa diedit
        ├── Area Elektronik       ← label: "Area", dibuat manual
        │   ├── Rak A1            ← label: "Rak", dibuat manual
        │   │   └── Slot A1-01    ← tanpa label, dibuat manual
        │   └── Rak A2
        └── Area Pakaian          ← label: "Area"
            └── Rak B1
```

### 10.4 Form Lokasi Stok

```
[Form Lokasi Stok]
│
├── Cabang               [required, dropdown]
├── Gudang               [required, dropdown — difilter oleh cabang yang dipilih]
├── Induk Lokasi         [optional, dropdown depth-aware — hanya lokasi dalam gudang yang sama]
│                        Dropdown menampilkan prefix "— " per level untuk visualisasi kedalaman
├── Kode Lokasi          [required, uppercase, regex: ^[A-Z0-9_-]+$, unique per tenant]
├── Nama Lokasi          [required]
└── Label Lokasi         [optional, teks bebas — mis. Rak, Lantai, Laci, Blok]
```

### 10.5 Aturan Bisnis Lokasi Stok

- Lokasi sistem (`system_type IS NOT NULL`) **tidak bisa dibuat, diedit, atau dihapus manual**
- **Deactivate**: otomatis cascade ke **seluruh descendant** (tidak perlu nonaktifkan satu per satu)
- **Delete**: diblokir jika masih punya children — sistem menampilkan pesan error
- Lokasi induk harus berada di **gudang yang sama**
- Hanya tampil lokasi aktif di dropdown saat membuat lokasi baru
- Circular reference diblokir di controller (tidak bisa memilih descendant sendiri sebagai parent)

---

## 11. Modul Master Produk — Brand

### 11.1 Deskripsi Bisnis

Merek/brand dari produk yang dijual. Data sederhana, digunakan sebagai referensi saat membuat produk.

### 11.2 Form Brand

```
├── Nama Brand  [required, unique per tenant]
└── Status      [checkbox aktif/nonaktif]
```

### 11.3 Alur Bisnis

- Brand dinonaktifkan → tidak muncul di dropdown saat buat/edit produk
- Brand yang sudah dipakai produk tetap tampil pada produk yang sudah ada

---

## 12. Modul Master Produk — Kategori

### 12.1 Deskripsi Bisnis

Kategori produk yang bersifat **hierarkis** (parent-child). Kategori juga menjadi trigger untuk menampilkan **atribut dinamis** pada form produk.

### 12.2 Hierarki Kategori (Contoh)

Hierarki tidak dibatasi levelnya (unlimited depth):

```
Elektronik
├── Laptop
│   ├── Gaming Laptop
│   │   └── High-End Gaming
│   └── Ultrabook
└── Handphone
    ├── Android
    └── iPhone

Pakaian
├── Pria
└── Wanita
```

### 12.3 Form Kategori

```
[Form Kategori]
│
├── Kategori Induk  [optional, dropdown depth-aware semua kategori aktif]
│                   Dropdown menampilkan prefix "— " per level untuk visualisasi kedalaman
├── Nama Kategori   [required]
├── Kode Kategori   [optional, unique per tenant]
└── Status          [checkbox]
```

### 12.4 Aturan Bisnis Kategori

- **Deactivate**: otomatis cascade ke **seluruh descendant** (tidak perlu nonaktifkan satu per satu)
- **Delete**: diblokir jika masih punya sub-kategori — sistem menampilkan pesan error
- Circular reference diblokir (tidak bisa memilih descendant sendiri sebagai parent)

### 12.5 Relasi Kategori dengan Atribut

Kategori bisa memiliki **banyak atribut dinamis** (via `category_attribute_sets` pivot table). Ketika user memilih kategori saat membuat produk, form produk akan **menampilkan field atribut tambahan** sesuai definisi atribut yang terhubung ke kategori tersebut.

---

---

## 13. Modul Master Produk — Satuan (UoM)

### 13.1 Deskripsi Bisnis

Unit of Measure — satuan pengukuran produk. Contoh: PCS, BOX, LUSIN, KG, LITER. Digunakan untuk konversi unit saat transaksi jual/beli.

### 13.2 Form Satuan

```
├── Kode Satuan  [required, unique per tenant, auto-uppercase]
└── Nama Satuan  [required]
```

---

## 14. Modul Master Produk — Atribut Produk

### 14.1 Deskripsi Bisnis

Mendefinisikan field tambahan yang bersifat dinamis untuk produk berdasarkan kategorinya. Contoh: kategori "Laptop" bisa memiliki atribut "Ukuran Layar", "RAM", "Prosesor". Kategori "Pakaian" bisa memiliki "Ukuran", "Bahan".

### 14.2 Tipe Data Atribut

| Tipe | Tampilan di Form Produk |
|---|---|
| `text` | Input teks bebas |
| `number` | Input angka |
| `select` | Dropdown pilihan |
| `boolean` | Toggle ya/tidak |

### 14.3 Form Definisi Atribut

```
[Form Atribut Produk]
│
├── Key              [required, lowercase, format: ^[a-z0-9_]+$, unique per tenant]
│                    Contoh: screen_size, ram_gb, material
│
├── Label            [required, nama ramah pengguna]
│                    Contoh: "Ukuran Layar", "Kapasitas RAM"
│
├── Tipe Data        [required: number/text/select/boolean]
│
├── Unit             [optional, misal: "cm", "GB", "inch"]
│                    Ditampilkan di samping input di form produk
│
├── Opsi (Options)   [required jika tipe=select]
│                    Format: koma-dipisahkan, misal: "S, M, L, XL"
│
├── Wajib Diisi      [checkbox: apakah field ini required di form produk]
│
└── Kategori Terkait [multi-select, pilih satu atau lebih kategori]
```

### 14.4 Dampak ke Form Produk

Saat pengguna **memilih kategori** di form produk → form secara dinamis menampilkan field-field atribut yang terdefinisi untuk kategori tersebut.

---

## 15. Modul Master Produk — Produk (SKU/Item)

### 15.1 Deskripsi Bisnis

Inti dari sistem — mendefinisikan semua produk yang diperjualbelikan. Setiap produk memiliki identitas (SKU, nama, brand, kategori), unit pengukuran (termasuk konversi multi-unit), atribut dinamis, dan harga.

### 15.2 Halaman Index — Daftar Produk

```
[Daftar Produk]
│
├── FILTER BAR
│   ├── Search        → Nama atau kode SKU
│   ├── Filter Brand  → Dropdown (saling mempengaruhi dengan filter kategori)
│   ├── Filter Kategori → Dropdown (saling mempengaruhi dengan filter brand)
│   └── Filter Status → Aktif / Tidak Aktif / Semua
│
├── TABEL HASIL
│   ├── Kolom: Kode SKU, Nama, Brand, Kategori, Satuan Dasar, Harga Modal, Harga Jual, Status, Aksi
│   └── Pagination 10 per halaman
│
└── TOMBOL AKSI PER BARIS
    ├── [Lihat Detail]  → /admin/items/{id}
    ├── [Edit]          → /admin/items/{id}/edit
    ├── [Nonaktifkan]   → PATCH /admin/items/{id}/deactivate
    └── [Barcode]       → /admin/items/{id}/barcodes
```

**Filter Brand & Kategori bersifat cascading:**
- Pilih brand tertentu → filter kategori hanya tampilkan kategori yang punya produk dari brand tersebut
- Berlaku sebaliknya juga

### 15.3 Form Create/Edit Produk

Form produk dibagi menjadi beberapa **seksi** untuk kemudahan pengisian:

#### Seksi 1: Identitas Produk

```
├── Kode SKU         [required, unique per tenant, auto-uppercase]
├── Nama Produk      [required]
├── Brand            [optional, dropdown brand aktif]
├── Kategori         [optional, dropdown kategori aktif]
│                    ↓ Saat dipilih: munculkan field atribut dinamis (Seksi 4)
└── Status Aktif     [checkbox, default: aktif]
```

#### Seksi 2: Unit & Kemasan

```
├── Satuan Dasar Stok (Base UOM)
│   ├── [required, dropdown UOM aktif]
│   └── Semua pergerakan stok dicatat dalam satuan ini
│
├── Default Satuan Jual
│   ├── [optional, default = satuan dasar]
│   └── Harus berupa satuan dasar atau satuan tambahan dengan allow_sale=true
│
└── Default Satuan Beli
    ├── [optional, default = satuan dasar]
    └── Harus berupa satuan dasar atau satuan tambahan dengan allow_purchase=true
```

#### Seksi 3: Satuan Tambahan (Konversi Unit)

Tabel dinamis — bisa tambah/hapus baris:

```
┌─────────────┬──────────────────────────────┬─────────────┬──────────────┬────────┐
│  Satuan     │  Konversi ke Satuan Dasar    │ Boleh Dijual│ Boleh Dibeli │ Hapus  │
├─────────────┼──────────────────────────────┼─────────────┼──────────────┼────────┤
│ BOX (drpdn) │  12  (berarti 1 BOX = 12 PCS)│     ✅      │     ✅       │   🗑   │
│ LUSIN       │  12                          │     ✅      │     ❌       │   🗑   │
└─────────────┴──────────────────────────────┴─────────────┴──────────────┴────────┘
[+ Tambah Satuan]
```

**Validasi unit:**
- Tidak boleh duplikat unit
- Satuan dasar tidak boleh masuk tabel ini
- Konversi harus > 0
- Default satuan jual harus punya `allow_sale = true`
- Default satuan beli harus punya `allow_purchase = true`

#### Seksi 4: Atribut Kategori (Dinamis)

> Hanya muncul jika kategori dipilih dan kategori tersebut punya atribut

```
[Contoh jika kategori = Laptop]
├── Ukuran Layar (text, unit: "inch")   [required jika is_required=true]
├── RAM (number, unit: "GB")
├── Prosesor (select: Core i3/Core i5/Core i7)
└── Backlit Keyboard (boolean: Ya/Tidak)
```

#### Seksi 5: Custom Fields

Pasangan key-value bebas untuk data tambahan yang tidak tercakup atribut standar:

```
┌───────────────────┬────────────────────────┬────────┐
│  Key              │  Value                 │ Hapus  │
├───────────────────┼────────────────────────┼────────┤
│ imei_number       │ 123456789012345        │   🗑   │
│ warranty_months   │ 12                     │   🗑   │
└───────────────────┴────────────────────────┴────────┘
[+ Tambah Field]
```

#### Seksi 6: Harga & Stok

```
├── Harga Modal (Cost Price)
│   ├── Input per satuan beli default
│   └── Jika satuan beli = BOX (12 pcs): harga modal / 12 = harga modal per pcs
│
├── Harga Jual Normal
│   └── Input per satuan jual default
│
├── Harga Jual Minimum
│   └── Harus ≤ Harga Jual Normal
│
├── Dapat Distok (is_stockable)
│   └── Checkbox: apakah produk ini tracking inventorinya?
│
└── PPN Sudah Termasuk (tax_included)
    └── Checkbox: apakah harga jual sudah include pajak?
```

### 15.4 Halaman Detail Produk

```
[Detail Produk]
│
├── Informasi lengkap semua field produk
├── Tabel semua unit (satuan dasar + satuan tambahan)
├── Tabel barcode terdaftar (dengan penanda barcode primer)
├── Daftar harga dari price list (modul mendatang)
│
└── RIWAYAT AUDIT (10 terbaru)
    └── Siapa mengubah apa → nilai lama vs nilai baru → kapan
```

### 15.5 Alur Bisnis Lengkap Pembuatan Produk

```
[Mulai Buat Produk]
    │
    ▼
Isi Kode SKU & Nama
    │
    ▼
Pilih Brand (opsional) → Pilih Kategori (opsional)
    │                           │
    │                           ▼
    │                   Form atribut kategori muncul
    │                   (JavaScript dynamic rendering)
    │
    ▼
Pilih Satuan Dasar Stok
    │
    ▼
Tentukan Satuan Jual & Beli default
    │
    ▼
Tambah konversi satuan tambahan (jika perlu)
    │
    ▼
Isi atribut kategori (jika ada)
    │
    ▼
Isi custom field (jika perlu)
    │
    ▼
Set harga modal & jual
    │
    ▼
Submit
    │
    ├── Validasi semua field
    ├── Validasi logika unit (no duplicate, konversi > 0, dll.)
    │
    ▼
Database Transaction:
    ├── Buat record Item
    ├── Buat record ItemUnit untuk satuan dasar (is_base=true)
    └── Buat record ItemUnit untuk setiap satuan tambahan
    │
    ▼
Redirect ke list produk dengan pesan sukses ✅
```

---

## 16. Manajemen Barcode

### 16.1 Deskripsi Bisnis

Setiap produk bisa memiliki **banyak barcode** (satu produk bisa punya barcode berbeda untuk satuan berbeda). Satu barcode ditandai sebagai **primer** yang digunakan POS untuk scanning utama.

### 16.2 Halaman Kelola Barcode

```
[Kelola Barcode — /admin/items/{id}/barcodes]
│
├── BREADCRUMB: Produk → [Nama Produk] → Barcode
│
├── FORM TAMBAH BARCODE
│   ├── Barcode        [required, unique per tenant]
│   ├── Satuan         [optional, dropdown unit produk ini]
│   └── Set sebagai Primer [checkbox]
│
└── TABEL BARCODE TERDAFTAR
    ├── Kolom: Barcode, Satuan, Primer?, Aksi
    └── Aksi per baris:
        ├── [Set Primer] → PATCH set-primary  (muncul jika bukan primer)
        └── [Hapus]      → DELETE barcode
```

### 16.3 Aturan Bisnis Barcode

| Kondisi | Perilaku Sistem |
|---|---|
| Barcode pertama ditambahkan | Otomatis dijadikan primer |
| Set barcode lain sebagai primer | Barcode primer sebelumnya menjadi non-primer |
| Hapus barcode yang primer | Barcode berikutnya otomatis dipromosikan jadi primer |
| Barcode harus unik | Per tenant — barcode sama tidak bisa ada di 2 produk aktif |
| Produk di-soft-delete | Semua barcode produk ikut soft-delete otomatis (cascade via Observer) |
| Produk di-restore | Barcode yang belum diklaim produk lain di-restore; yang sudah diklaim dilewati |
| Tambah barcode duplikat | Dicek dengan `SELECT ... FOR UPDATE` di dalam transaksi — race condition aman |

---

## 17. Import Produk via Excel

### 17.1 Deskripsi Bisnis

Fitur untuk menambahkan banyak produk sekaligus melalui upload file Excel. Berguna saat onboarding tenant baru atau saat ada perubahan katalog massal.

### 17.2 Alur Import

```
[Halaman Import — /admin/items/import]
    │
    ├── Upload file .xlsx / .xls (maks 20MB)
    │
    ▼
[POST /admin/items/import]
    │
    ├── ExcelImportService memproses file
    ├── Validasi setiap baris data
    │   ├── Baris valid → antri untuk dibuat
    │   └── Baris error → dicatat di error report
    │
    ▼
Hasil import ditampilkan:
    ├── Jumlah berhasil diimport
    └── Daftar error per baris (jika ada)
```

---

## 18. Alur Data Lintas Modul (Cross-Module Flows)

### 18.1 Alur Setup Awal Tenant Baru

```
[SuperAdmin membuat Tenant baru]
    │
    ▼
[Buat Cabang pertama]
    │
    └── Otomatis: Gudang Utama + Lokasi Stok Root dibuat
    │
    ▼
[Setup Master Data]
    ├── Buat Satuan (UoM): PCS, BOX, dll.
    ├── Buat Brand
    ├── Buat Kategori (+ Atribut jika perlu)
    └── Buat Produk (SKU) dengan unit & harga
    │
    ▼
[Buat User]
    ├── Owner/Admin untuk tenant
    └── Manager/Staff untuk cabang
    │
    ▼
[Tenant siap beroperasi] ✅
```

### 18.2 Alur Setup Produk Baru (Dependensi)

```
[Prasyarat sebelum buat produk:]

UoM harus ada dulu
    → untuk mengisi Satuan Dasar, Satuan Jual, Satuan Beli

Brand (opsional) harus ada dulu
    → untuk mengisi field Brand produk

Kategori (opsional) harus ada dulu
    → untuk mengisi field Kategori produk
    → untuk memunculkan atribut dinamis

Atribut Produk (opsional) harus ada & terhubung ke kategori
    → untuk memunculkan field atribut di form produk

[Baru bisa buat Produk] ✅
```

### 18.3 Alur Deaktivasi yang Aman

```
Nonaktifkan Kategori:
    → Observer otomatis cascade ke seluruh sub-kategori descendant
    → Tidak perlu nonaktifkan satu per satu

Nonaktifkan Lokasi Stok:
    → Observer otomatis cascade ke seluruh lokasi descendant
    → Tidak perlu nonaktifkan satu per satu

Hapus Kategori / Lokasi Stok:
    → Diblokir jika masih punya children
    → Hapus children dulu, baru parent bisa dihapus

Nonaktifkan Gudang:
    → Cek: tipe bukan MAIN
    → Semua lokasi di gudang ini sebaiknya nonaktif dulu

Nonaktifkan Cabang:
    → Semua gudang & lokasi terkait ikut nonaktif secara logis
```

---

## 19. Hirarki Data & Relasi Model

### 19.1 Peta Relasi Model Utama

```
Tenant (root semua data)
│
├── Branch (Cabang)
│   ├── Warehouse (Gudang)
│   │   └── StockLocation (Lokasi Stok, hierarkis self-referencing, unlimited depth)
│   └── User (Staff cabang)
│
├── Item (Produk/SKU)
│   ├── Brand
│   ├── Category (hierarkis self-referencing, unlimited depth)
│   │   └── AttributeDefinition (many-to-many via category_attribute_sets)
│   ├── Uom (via base_uom_id, selling_uom_id, purchase_uom_id)
│   ├── ItemUnit[] (semua unit konversi)
│   │   └── Uom
│   └── ItemBarcode[] (soft-deletable, cascade dari Item)
│
├── PriceList
│   └── PriceListItem[]
│
├── InventoryLedger (append-only)
│
└── StockBalance (materialized balance per item per lokasi)
```

### 19.2 Tabel Peran Tiap Model

| Model | Peran Bisnis | Scope |
|---|---|---|
| Tenant | Perusahaan/brand ritel | Platform |
| Branch | Unit operasional (toko/kantor) | Per Tenant |
| Warehouse | Tempat fisik penyimpanan stok | Per Branch |
| StockLocation | Lokasi spesifik dalam gudang (rak, area) | Per Warehouse |
| User | Pengguna sistem | Per Branch |
| Item | Produk yang diperjualbelikan (SKU) | Per Tenant |
| ItemUnit | Definisi konversi unit per produk | Per Item |
| ItemBarcode | Barcode untuk scanning | Per Item |
| Brand | Merek produk | Per Tenant |
| Category | Klasifikasi produk (hierarkis) | Per Tenant |
| Uom | Satuan pengukuran | Per Tenant |
| AttributeDefinition | Field dinamis produk per kategori | Per Tenant |
| AuditLog | Riwayat perubahan data (immutable) | Per Tenant |
| PriceList | Daftar harga per cabang | Per Branch |
| InventoryLedger | Jurnal pergerakan stok (immutable, append-only) | Per Branch |
| StockBalance | Saldo stok terkini per item per lokasi (materialized) | Per Tenant |

---

## 20. Multi-Tenancy & Isolasi Data

### 20.1 Prinsip Isolasi

Setiap query ke database **otomatis disaring** berdasarkan `tenant_id` pengguna yang login. Ini diterapkan lewat Laravel Eloquent Global Scope.

```
User login → Middleware ekstrak tenant_id dari user
                ↓
Setiap query model → WHERE tenant_id = {tenant_id}
                ↓
Data tenant lain tidak pernah ikut dalam result set
```

### 20.2 Hierarki Visibilitas Data

```
SuperAdmin
  → Lihat SEMUA data, SEMUA tenant
  → Bisa filter per tenant di UI

Owner / Admin (Global Role)
  → Lihat semua data DALAM tenant sendiri
  → Bisa filter per cabang

Manager / Staff (Branch Role)
  → Lihat data cabang sendiri saja
  → Tidak ada filter cabang (sudah otomatis)
```

### 20.3 Validasi Kepemilikan Saat Form Submit

Setiap form yang memiliki foreign key (misal `branch_id`, `category_id`) divalidasi:
```
Rule::exists('table', 'id')->where('tenant_id', $currentTenantId)
```
Ini mencegah user "menyelundupkan" ID dari tenant lain ke dalam data mereka.

---

## 21. Pola Konsistensi UI

### 21.1 Pola CRUD Standar

Semua modul mengikuti pola yang konsisten:

| Action | Method | URL Pattern | Redirect Setelah |
|---|---|---|---|
| List | GET | `/admin/{resource}` | — |
| Create Form | GET | `/admin/{resource}/create` | — |
| Store | POST | `/admin/{resource}` | → list + flash success |
| Edit Form | GET | `/admin/{resource}/{id}/edit` | — |
| Update | PUT | `/admin/{resource}/{id}` | → list + flash success |
| Deactivate | PATCH | `/admin/{resource}/{id}/deactivate` | → list + flash success |
| Reactivate | PATCH | `/admin/{resource}/{id}/reactivate` | → list + flash success |
| Delete | DELETE | `/admin/{resource}/{id}` | → list + flash success / error |

> Delete diblokir via Observer jika masih ada relasi children (kategori, lokasi stok). Flash error ditampilkan tanpa exception page.

### 21.2 Pola Filter & Search

Semua halaman list memiliki:
- **Form filter** yang submit via GET
- **Query string dipertahankan** saat pagination (`.withQueryString()`)
- Filter otomatis **tersimpan di URL** — bisa di-bookmark atau di-share

### 21.3 Pola Status Aktif/Nonaktif

Sistem menggunakan **deaktivasi** (soft-disable) bukan penghapusan:
- Semua master data bisa dinonaktifkan
- Data nonaktif tidak muncul di dropdown form lain
- Data nonaktif masih bisa diaktifkan kembali
- Data yang sudah dipakai (referential integrity) tidak bisa dihapus begitu saja

### 21.4 Pola Pesan Flash

Setelah setiap aksi berhasil, muncul **flash message** di atas halaman:
- ✅ Hijau → Berhasil (created, updated, deactivated, reactivated)
- ❌ Merah → Error validasi atau kesalahan bisnis

### 21.5 Penanganan Error Form

- Error validasi ditampilkan **inline di bawah field** yang bermasalah
- Form diisi ulang dengan nilai yang sudah diinput (old values)
- User tidak perlu isi ulang form dari awal

---

## 22. Modul Yang Akan Datang

Sistem sudah menyiapkan **pondasi database** untuk modul-modul ini, meski UI belum tersedia:

### Modul 04: Harga & Diskon
- Kelola price list per cabang
- Harga spesial per customer/grup
- Manajemen diskon & promo
- *Model sudah ada: `PriceList`, `PriceListItem`*

### Modul 05: Inventori
- Penerimaan stok
- Stock opname (stock count)
- Transfer antar gudang/lokasi
- Jurnal pergerakan stok
- *Model sudah ada: `InventoryLedger` (append-only), `StockBalance` (materialized balance, O(1) query)*

### Modul 06: POS Transaksi
- Transaksi penjualan kasir
- Scanning barcode produk
- Pilih metode pembayaran
- Cetak struk
- *Bergantung pada: Item, Barcode, PriceList, InventoryLedger*

### Modul 07: Delivery Order
- Manajemen pesanan pengiriman
- Proses picking & packing
- Tracking status pengiriman

### Modul 08: Piutang (AR)
- Kelola kredit pelanggan
- Tracking invoice & pembayaran
- Laporan aging piutang

### Modul 09: Pembelian
- Purchase order ke supplier
- Penerimaan barang
- Manajemen supplier

### Modul 10: Akuntansi
- General ledger
- Laporan keuangan (Laba Rugi, Neraca)
- Rekonsiliasi akun

---

*Dokumen ini dihasilkan berdasarkan analisis kode sumber pada branch `mod-03-branch-warehouse`. Terakhir diperbarui: 2026-03-28.*
