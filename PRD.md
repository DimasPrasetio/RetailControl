# PRD

## Mini ERP Multi-Tenant untuk Toko Material Bahan Bangunan

## 1. Informasi Dokumen

**Nama Produk:** Mini ERP Material
**Jenis Produk:** Web-based Multi-Tenant ERP
**Target Pengguna:** Toko material bahan bangunan
**Aktor Utama:** Super Admin, Owner, Admin, Kasir
**Aktor Operasional Non-System:** PIC Gudang, Supir, Pelanggan
**Status Dokumen:** PRD
**Tujuan Dokumen:** Menjadi acuan produk, desain, engineering, QA, dan implementasi bisnis

---

# 2. Latar Belakang

Operasional toko material bahan bangunan memiliki karakteristik yang berbeda dari retail umum. Produk sangat beragam, atribut produk tidak seragam, lokasi penyimpanan tersebar, proses penjualan dapat berupa transaksi langsung atau pengiriman, pembayaran fleksibel, serta banyak alur operasional tetap bergantung pada dokumen fisik dan tanda tangan manual.

Sistem yang dibutuhkan bukan hanya POS atau inventory biasa, tetapi platform operasional terintegrasi dari procurement, inventory, sales, delivery, transfer antar gudang, piutang/hutang, hingga laporan keuangan dan pajak.

Selain untuk satu toko, sistem ini juga dirancang agar dapat digunakan oleh banyak toko material lain dengan workflow serupa melalui arsitektur multi-tenant, tanpa data antar toko tercampur.

---

# 3. Tujuan Produk

## 3.1 Tujuan Utama

Membangun aplikasi mini ERP berbasis web untuk toko material yang mampu mengelola proses operasional inti dari hulu ke hilir, mulai dari pembelian barang hingga penjualan, distribusi, dan pelaporan keuangan.

## 3.2 Tujuan Bisnis

* Mengurangi kebocoran operasional dan keuangan
* Menjaga akurasi stok lintas toko dan gudang
* Meningkatkan keterlacakan transaksi, pembayaran, dan dokumen
* Mempermudah monitoring owner
* Menjadi fondasi SaaS multi-tenant untuk banyak toko material

## 3.3 Tujuan Operasional

* Menyatukan pembelian, stok, penjualan, pengiriman, dan keuangan dalam satu sistem
* Menyesuaikan sistem dengan realitas lapangan, bukan memaksa lapangan menyesuaikan sistem
* Memungkinkan proses tetap berjalan walau gudang dan supir belum menjadi user aktif sistem

---

# 4. Ruang Lingkup

## 4.1 In Scope

* Multi-tenant
* Multi-toko/cabang per tenant
* Master produk fleksibel berbasis atribut dinamis
* Multi gudang dan multi area penyimpanan
* Procurement
* Inventory management
* Penjualan langsung
* Penjualan dengan pengiriman
* Surat jalan
* Split shipment
* Transfer antar gudang
* Piutang pelanggan
* Hutang supplier
* Laporan keuangan dasar
* Laporan laba/rugi
* Laporan pajak dasar
* QR code scanning untuk kasir
* Pencetakan dokumen dari komputer admin

## 4.2 Out of Scope Fase Awal

* Mobile app native khusus gudang/supir
* Integrasi GPS live tracking kendaraan
* Integrasi akuntansi tingkat enterprise penuh
* Integrasi e-commerce marketplace
* OCR dokumen fisik otomatis
* Digital signature legal formal
* Integrasi bank otomatis
* Payroll
* HRIS

---

# 5. Pemangku Kepentingan

## 5.1 Internal

* Owner toko
* Admin toko
* Kasir
* Super Admin platform
* Tim pengembang
* Tim support

## 5.2 Eksternal

* Supplier
* Pelanggan
* Supir
* PIC gudang
* Auditor/pihak keuangan internal toko

---

# 6. Profil Pengguna

## 6.1 Super Admin

Mengelola platform multi-tenant, tenant baru, konfigurasi global, monitoring seluruh tenant, dan menjaga isolasi data.

## 6.2 Owner

Melihat laporan operasional dan keuangan, memantau piutang/hutang, performa cabang, laba/rugi, dan status bisnis secara keseluruhan.

## 6.3 Admin

Mengelola data produk, supplier, customer, procurement, stok, surat jalan, transfer gudang, piutang, hutang, cetak dokumen, dan finalisasi operasional.

## 6.4 Kasir

Melakukan transaksi penjualan, scan QR code, input pembayaran, cetak nota melalui admin print station, dan melihat informasi stok dasar.

## 6.5 PIC Gudang (Non-System Actor)

Menyiapkan barang berdasarkan dokumen fisik, menandatangani surat, menyerahkan barang ke supir, menerima barang transfer.

## 6.6 Supir (Non-System Actor)

Membawa surat jalan fisik, menandatangani dokumen, mengirimkan barang, memperoleh tanda tangan pelanggan, mengembalikan dokumen ke admin.

---

# 7. Masalah Utama yang Diselesaikan

1. Data produk tidak seragam dan sulit dikelola
2. Satu produk dapat berasal dari banyak supplier
3. Lokasi stok tersebar dan struktur gudang tidak konsisten
4. Transaksi penjualan punya banyak model
5. Surat jalan bersifat fisik dan multi-tahap
6. Status pembayaran bisa berubah setelah pengiriman selesai
7. Satu invoice bisa memiliki beberapa surat jalan
8. Barang rusak saat pengiriman perlu penggantian
9. Transfer antar gudang perlu dokumen dan status transit
10. Owner membutuhkan laporan untung/rugi dan pajak
11. Banyak kasir melakukan transaksi dari tablet, tetapi printer berada di admin PC
12. Sistem harus siap untuk banyak toko lain dengan data terisolasi

---

# 8. Prinsip Desain Produk

1. **Business-first**
   Sistem mengikuti alur bisnis toko material.

2. **Operationally realistic**
   Gudang dan supir boleh tetap bekerja dengan kertas.

3. **Flexible product modeling**
   Produk tidak dipaksa memiliki atribut seragam.

4. **Document-driven workflow**
   Banyak proses bergantung pada dokumen operasional.

5. **Inventory traceability**
   Semua pergerakan stok harus dapat ditelusuri.

6. **Tenant isolation first**
   Data antar tenant tidak boleh bercampur.

7. **Role-based access**
   Setiap aktor hanya melihat dan mengerjakan hal yang relevan.

8. **Human-friendly UI**
   UI untuk admin dan kasir harus sederhana dan non-teknis.

---

# 9. Asumsi Sistem

* Sistem berbasis web
* Digunakan di desktop/admin PC dan tablet kasir
* Gudang dan supir tidak diwajibkan login ke sistem pada fase awal
* Tanda tangan tetap dilakukan di atas kertas
* Pencetakan dokumen dilakukan melalui komputer admin di lokasi toko
* Produk memiliki QR code untuk memudahkan scanning
* Tenant dapat memiliki lebih dari satu toko/cabang
* Satu tenant dapat memiliki lebih dari satu gudang
* Pembayaran dapat bersifat langsung, COD, transfer awal, atau utang

---

# 10. Arsitektur Bisnis Tingkat Tinggi

## Modul Utama

1. Tenant & Organization Management
2. User, Role & Access Control
3. Master Produk & Atribut Dinamis
4. Supplier & Customer Management
5. Procurement
6. Inventory & Warehouse Management
7. POS / Sales
8. Delivery / Surat Jalan
9. Transfer Antar Gudang
10. Piutang & Hutang
11. Keuangan & Pajak
12. Reporting & Dashboard
13. Print Management
14. Audit Trail & Activity Log

---

# 11. Kebutuhan Fungsional per Modul

---

## 11.1 Modul Multi-Tenant & Organisasi

### Tujuan

Mendukung banyak toko material dalam satu platform dengan isolasi data penuh.

### Fitur

* Registrasi tenant baru
* Tenant memiliki identitas sendiri
* Tenant dapat memiliki satu atau banyak toko/cabang
* Tenant dapat memiliki satu atau banyak gudang
* Semua data transaksi, user, produk, dan laporan dipisahkan per tenant
* Super admin dapat melihat seluruh tenant
* Owner hanya dapat melihat tenant miliknya
* Admin dan kasir hanya dapat mengakses data tenant dan cabang yang ditentukan

### Kebutuhan Khusus

* Semua tabel operasional wajib memiliki konteks tenant
* Tidak boleh ada kebocoran data antar tenant

---

## 11.2 Modul User, Role & Access Control

### Role

* Super Admin
* Owner
* Admin
* Kasir

### Hak Akses Dasar

**Super Admin**

* Kelola tenant
* Monitoring lintas tenant
* Melihat seluruh data sistem

**Owner**

* Melihat laporan, transaksi, stok, keuangan
* Melihat performa toko/gudang/cabang
* Approval kebijakan tertentu bila dibutuhkan

**Admin**

* Kelola master data
* Procurement
* Inventory
* Sales delivery
* Surat jalan
* Transfer gudang
* Piutang/hutang
* Cetak dokumen

**Kasir**

* Input transaksi penjualan
* Scan QR code
* Pilih customer
* Input pembayaran
* Cetak nota via print queue
* Akses terbatas ke stok dan harga

### Fitur

* Login
* Logout
* Session management
* Role-based menu
* Permission per modul dan tindakan
* Audit siapa melakukan apa

---

## 11.3 Modul Master Produk & Atribut Dinamis

### Tujuan

Mengelola produk material dengan struktur data fleksibel sesuai kategori.

### Tantangan

Produk seperti pipa, keramik, semen, paku, cat, bata, besi, dan lain-lain memiliki atribut berbeda.

### Fitur

* Master kategori produk
* Master brand opsional
* Master attribute definition per kategori
* Produk dapat memiliki atribut berbeda sesuai kategori
* Produk dapat memiliki brand atau tanpa brand
* Produk dapat memiliki SKU/kode internal
* Produk dapat memiliki QR code / barcode
* Produk dapat memiliki beberapa satuan
* Produk dapat memiliki harga modal
* Produk dapat memiliki harga normal
* Produk dapat memiliki harga minimum
* Produk dapat diaktifkan/nonaktifkan

### Contoh Atribut

**Pipa**

* brand
* tipe
* model
* diameter inch
* diameter mm
* panjang
* warna

**Semen**

* brand
* jenis
* berat
* kemasan

**Keramik**

* brand
* ukuran
* motif
* grade
* isi per box

**Paku**

* ukuran
* jenis
* isi per pack

### Requirement Detail

* Attribute bersifat dinamis per kategori
* Tidak menggunakan struktur kolom tetap untuk semua jenis atribut
* Produk dapat ditandai sebagai stokable / non-stokable bila diperlukan
* Produk dapat memiliki unit conversion, contoh:

  * pack → pcs
  * box → pcs
  * sak → kg
* Produk dapat memiliki minimal selling price untuk kontrol negosiasi

### Validasi

* Kasir/admin tidak boleh menyimpan transaksi di bawah harga minimum tanpa otorisasi jika kebijakan diterapkan
* Produk wajib memiliki satuan dasar stok

---

## 11.4 Modul Supplier & Customer Management

### Supplier

* Tambah/edit/hapus supplier
* Satu produk dapat berasal dari banyak supplier
* Menyimpan informasi supplier
* Riwayat pembelian per supplier
* Hutang supplier

### Customer

* Tambah/edit/hapus customer
* Customer umum / customer langganan
* Menyimpan alamat pengiriman
* Menyimpan histori transaksi
* Menyimpan status piutang
* Menyimpan batas kredit opsional

---

## 11.5 Modul Procurement

### Tujuan

Mengelola pembelian barang dari supplier.

### Fitur

* Purchase order
* Penerimaan barang
* Faktur supplier
* Hutang supplier
* Pembelian produk dari multi supplier
* Harga beli per supplier
* Histori harga beli

### Alur Dasar

1. Admin membuat pembelian
2. Pilih supplier
3. Input item dan qty
4. Input harga beli
5. Input gudang/lokasi tujuan
6. Simpan status pembelian
7. Saat barang diterima, stok bertambah
8. Jika pembayaran belum lunas, tercatat sebagai hutang

### Variasi

* Pembelian lunas
* Pembelian tempo/hutang
* Pembelian sebagian diterima
* Harga beli produk sama dapat berbeda antar supplier

### Data Penting

* nomor pembelian
* tanggal
* supplier
* item
* qty
* satuan
* harga beli
* diskon
* pajak
* total
* status bayar
* status terima
* gudang penerima

---

## 11.6 Modul Inventory & Warehouse Management

### Tujuan

Mengelola stok lintas toko, gudang, dan area penyimpanan.

### Konsep Lokasi

Lokasi bersifat hierarkis dan fleksibel:

* Toko / Gudang

  * Area

    * Sub Area (opsional)

### Fitur

* Master gudang
* Master area gudang
* Master sub area opsional
* Mapping lokasi stok
* Stok per lokasi
* Mutasi stok
* Histori mutasi
* Stock adjustment
* Stock opname
* Reserved stock
* In-transit stock
* Damaged stock bila dibutuhkan

### Requirement

* Sistem harus mendukung struktur area yang tidak konsisten
* Beberapa gudang bisa punya sub area, lainnya tidak
* Toko utama juga bisa dianggap lokasi stok
* Barang besar seperti keramik dan material berat dapat disimpan di lokasi berbeda

### Inventory Ledger

Setiap perubahan stok wajib tercatat dengan:

* tanggal/waktu
* tenant
* toko/gudang
* lokasi
* produk
* qty masuk
* qty keluar
* satuan
* sumber transaksi
* referensi dokumen
* user pemroses

---

## 11.7 Modul Penjualan

Penjualan dibagi menjadi dua tipe utama:

### A. Penjualan Langsung / Walk-in

Transaksi pelanggan datang ke toko, bayar langsung atau berutang, lalu pulang tanpa surat jalan.

### B. Penjualan dengan Pengiriman

Transaksi dibuat di sistem, barang disiapkan gudang, dikirim supir, dan diselesaikan setelah dokumen kembali.

---

## 11.8 Modul POS / Penjualan Langsung

### Fitur

* Transaksi dari tablet kasir
* Scan QR code
* Cari produk manual
* Pilih customer atau customer umum
* Input qty
* Input harga jual
* Validasi harga minimum
* Input pembayaran:

  * tunai
  * transfer
  * utang
* Cetak nota
* Sinkron ke admin print station
* Update stok otomatis setelah transaksi final

### Output

* Nota penjualan
* Rekam pembayaran
* Jika utang, buat piutang pelanggan

### Validasi

* Tidak perlu surat jalan
* Barang keluar langsung saat transaksi selesai
* Transaksi bisa dibatalkan sesuai hak akses
* Transaksi harus memiliki jejak kasir

---

## 11.9 Modul Sales Delivery / Penjualan dengan Pengiriman

### Fitur

* Buat sales order / invoice
* Pilih customer
* Pilih alamat pengiriman
* Pilih item dan qty
* Cek stok
* Tentukan model pembayaran:

  * transfer di awal
  * COD
  * utang
  * pembayaran parsial jika diperlukan
* Buat surat jalan
* Boleh menghasilkan lebih dari satu surat jalan dari satu invoice
* Tracking status pengiriman
* Finalisasi setelah dokumen kembali

### Requirement Kritis

* 1 invoice dapat memiliki banyak surat jalan
* 1 surat jalan dapat memiliki supir yang berbeda
* Item invoice dapat dipecah ke beberapa pengiriman
* Sistem harus tahu item mana dikirim di surat jalan mana

### Status yang Disarankan

**Invoice Sales**

* Draft
* Confirmed
* Partially Delivered
* Fully Delivered
* Awaiting Document Return
* Completed
* Outstanding Receivable
* Cancelled

**Surat Jalan**

* Draft
* Printed
* Signed by Admin
* Prepared by Warehouse
* Signed by Warehouse PIC
* Assigned to Driver
* Signed by Driver
* Delivered
* Signed by Customer
* Returned to Admin
* Closed
* Issue Reported

---

## 11.10 Modul Surat Jalan

### Tujuan

Menjadi dokumen operasional resmi untuk pengiriman barang ke pelanggan.

### Fitur

* Generate surat jalan dari transaksi pengiriman
* Mendukung cetak 2 lembar
* Mendukung beberapa surat jalan per invoice
* Memuat item, qty, tujuan, customer, catatan
* Menyimpan status penandatanganan manual
* Menyimpan hasil pengantaran
* Menyimpan status pembayaran akhir

### Alur Bisnis

1. Admin membuat transaksi pengiriman
2. Sistem membuat surat jalan
3. Surat dicetak 2 lembar
4. Admin tanda tangan
5. Surat dibawa ke gudang
6. PIC gudang siapkan barang dan tanda tangan
7. Surat diserahkan ke supir
8. Supir tanda tangan
9. Barang dikirim ke pelanggan
10. Pelanggan tanda tangan saat menerima
11. Berdasarkan status pembayaran:

* jika lunas, lembar tertentu diberikan ke pelanggan
* jika utang, dokumen yang diperlukan diberikan sesuai SOP bisnis

12. Supir kembali ke toko
13. Dokumen diserahkan ke admin
14. Admin update status akhir di sistem:

* lunas
* piutang
* ada issue/rusak/kurang

### Catatan Penting

Karena bisnis masih berbasis kertas, sistem hanya merekam status setelah dokumen selesai diproses manual.

---

## 11.11 Modul Split Shipment

### Tujuan

Menangani kasus satu invoice memerlukan beberapa kendaraan/supir.

### Fitur

* Memecah item invoice ke beberapa surat jalan
* Tiap surat jalan punya supir dan kendaraan masing-masing
* Tiap surat jalan punya item dan qty masing-masing
* Tiap surat jalan punya tanda tangan manual sendiri
* Admin dapat melihat total invoice vs total item yang sudah dialokasikan ke seluruh surat jalan

### Validasi

* Total qty seluruh surat jalan tidak boleh melebihi qty invoice
* Satu item dapat terbagi ke lebih dari satu surat jalan bila dibutuhkan

---

## 11.12 Modul Kerusakan / Kekurangan Saat Pengiriman

### Tujuan

Mengelola kejadian barang rusak, pecah, kurang, atau perlu diganti setelah dikirim.

### Contoh Kasus

* Keramik pecah di jalan
* Bata belah
* Barang tidak utuh saat diterima pelanggan

### Fitur

* Catat issue pengiriman
* Catat item rusak/kurang
* Catat qty
* Catat tindakan:

  * penggantian
  * pengurangan tagihan
  * penjadwalan kirim ulang
* Catat referensi invoice dan surat jalan
* Catat dari stok mana penggantian diambil
* Catat apakah issue menimbulkan kerugian toko

### Status Issue

* Open
* Replacement Pending
* Replacement Delivered
* Resolved
* Written Off

---

## 11.13 Modul Transfer Antar Gudang

### Tujuan

Mengelola perpindahan stok antar gudang/toko secara resmi dan terlacak.

### Fitur

* Buat dokumen transfer
* Pilih gudang asal
* Pilih gudang tujuan
* Pilih item dan qty
* Cetak surat transfer
* Proses tanda tangan gudang asal
* Proses tanda tangan supir
* Proses tanda tangan gudang tujuan
* Finalisasi admin saat dokumen kembali

### Alur

1. Admin membuat transfer
2. Surat transfer dicetak
3. Gudang asal menyiapkan barang dan tanda tangan
4. Supir menerima dan tanda tangan
5. Barang dikirim ke gudang tujuan
6. PIC gudang tujuan menerima dan tanda tangan
7. Supir mengembalikan dokumen ke admin
8. Admin finalisasi transfer

### Inventory Logic

* Saat transfer dibuat: stok dapat di-reserve
* Saat barang keluar dari gudang asal: stok asal berkurang dan status menjadi in-transit
* Saat diterima gudang tujuan: stok tujuan bertambah
* Jika belum diterima: tetap tercatat sebagai in-transit

---

## 11.14 Modul Piutang Pelanggan

### Tujuan

Mencatat transaksi pelanggan yang belum lunas.

### Sumber Piutang

* Penjualan langsung dengan utang
* Penjualan pengiriman yang dibayar tempo
* Pengiriman COD yang belum diterima admin sebagai pembayaran final
* Pembayaran parsial

### Fitur

* Daftar piutang
* Histori pembayaran
* Status piutang
* Jatuh tempo
* Customer aging
* Pelunasan sebagian/penuh
* Catatan pembayaran dari supir/admin

### Status

* Belum jatuh tempo
* Jatuh tempo
* Sebagian dibayar
* Lunas
* Bermasalah

---

## 11.15 Modul Hutang Supplier

### Tujuan

Mencatat kewajiban pembelian yang belum dibayar.

### Fitur

* Daftar hutang supplier
* Histori pembayaran
* Jatuh tempo
* Pelunasan sebagian/penuh
* Rekap hutang per supplier

---

## 11.16 Modul Kas & Keuangan Dasar

### Tujuan

Memberi owner dan admin visibilitas atas arus uang masuk dan keluar.

### Fitur

* Rekam kas masuk
* Rekam kas keluar
* Pembayaran customer
* Pembayaran ke supplier
* Rekap pendapatan
* Rekap pengeluaran
* Rekap piutang/hutang
* Cashflow dasar

### Catatan

Fase awal fokus pada keuangan operasional, belum akuntansi enterprise penuh.

---

## 11.17 Modul Laba/Rugi

### Tujuan

Membantu admin dan owner menghitung performa usaha.

### Kebutuhan

* Penjualan kotor
* Diskon
* Retur/kerugian pengiriman
* HPP
* Laba kotor
* Beban operasional dasar
* Laba bersih sederhana

### Catatan

Akurasi laba/rugi bergantung pada:

* akurasi harga modal
* mutasi stok
* penanganan kerusakan
* penyesuaian stok

---

## 11.18 Modul Pajak

### Tujuan

Membantu admin melihat estimasi dan rekap pajak yang harus dibayarkan.

### Fitur Minimum

* Menandai transaksi kena pajak / non pajak
* Rekap pajak penjualan
* Rekap pajak pembelian
* Ringkasan dasar pajak periode bulanan/tahunan

### Catatan

Implementasi detail pajak harus disesuaikan dengan kebijakan bisnis dan regulasi yang berlaku pada tenant terkait.

---

## 11.19 Modul Reporting & Dashboard

### Dashboard Owner

* Penjualan hari ini / bulan ini / tahun ini
* Piutang outstanding
* Hutang outstanding
* Stok kritis
* Laba/rugi ringkas
* Performa cabang
* Performa gudang
* Performa kategori produk
* Delivery belum selesai
* Surat jalan belum kembali

### Dashboard Admin

* Pending procurement
* Pending delivery
* Pending transfer
* Piutang jatuh tempo
* Hutang jatuh tempo
* Issue pengiriman
* Stok menipis
* Aktivitas transaksi harian

### Laporan

* Penjualan harian, bulanan, tahunan
* Pembelian harian, bulanan, tahunan
* Laba rugi
* Rekap stok
* Mutasi stok
* Piutang
* Hutang
* Delivery status
* Surat jalan kembali / belum kembali
* Transfer antar gudang
* Barang rusak / issue pengiriman
* Pajak

---

## 11.20 Modul Print Management

### Tujuan

Mendukung kasir dari tablet tetapi pencetakan terpusat di komputer admin.

### Fitur

* Print queue
* Routing dokumen ke admin print station
* Cetak nota
* Cetak surat jalan
* Cetak surat transfer
* Reprint dengan audit log
* Status print pending / success / failed

### Requirement

* Kasir dapat mengirim permintaan cetak dari tablet
* Komputer admin menjadi print relay
* Dokumen tidak boleh hilang tanpa status
* Harus ada log siapa yang mencetak dan kapan

---

## 11.21 Modul Audit Trail

### Tujuan

Menjamin keterlacakan perubahan sistem.

### Fitur

* Catat create/update/delete
* Catat perubahan status dokumen
* Catat perubahan harga
* Catat perubahan stok
* Catat user, waktu, referensi dokumen
* Catat reprint dokumen
* Catat pembatalan transaksi

---

# 12. Alur Bisnis Utama

---

## 12.1 Alur Pembelian Barang dari Supplier

1. Admin membuat data pembelian
2. Memilih supplier
3. Memilih barang dan qty
4. Menentukan harga beli
5. Menentukan gudang/lokasi tujuan
6. Menentukan metode pembayaran
7. Menyimpan dokumen pembelian
8. Saat barang diterima, stok masuk
9. Jika belum dibayar lunas, terbentuk hutang supplier

---

## 12.2 Alur Penjualan Langsung

1. Kasir scan QR code atau cari barang
2. Input qty
3. Pilih customer atau umum
4. Sistem hitung total
5. Kasir input metode bayar
6. Jika lunas, transaksi selesai
7. Jika utang, piutang tercatat
8. Nota dicetak dari admin print station
9. Stok berkurang

---

## 12.3 Alur Penjualan dengan Pengiriman

1. Admin/kasir membuat transaksi
2. Pilih customer dan alamat kirim
3. Input item dan qty
4. Pilih metode pembayaran
5. Konfirmasi order
6. Buat satu atau lebih surat jalan
7. Dokumen dicetak
8. Gudang menyiapkan barang
9. Supir menerima barang
10. Barang dikirim
11. Pelanggan tanda tangan
12. Supir kembali membawa dokumen
13. Admin update status akhir
14. Jika belum lunas, masuk piutang

---

## 12.4 Alur Split Shipment

1. Invoice dibuat
2. Item dialokasikan ke beberapa surat jalan
3. Tiap surat jalan dipasangkan dengan supir/kendaraan
4. Masing-masing surat diproses manual
5. Status tiap surat dipantau
6. Invoice dianggap selesai jika seluruh item terselesaikan

---

## 12.5 Alur Issue Pengiriman

1. Saat barang diterima customer, ditemukan issue
2. Supir / dokumen mencatat adanya kerusakan/kekurangan
3. Admin input issue ke sistem
4. Admin menentukan tindak lanjut
5. Jika penggantian dilakukan, sistem buat referensi pengiriman pengganti
6. Issue ditutup setelah selesai

---

## 12.6 Alur Transfer Antar Gudang

1. Admin membuat transfer
2. Barang dipilih dari gudang asal
3. Surat transfer dicetak
4. Gudang asal menyiapkan dan tanda tangan
5. Supir menerima dan tanda tangan
6. Gudang tujuan menerima dan tanda tangan
7. Dokumen kembali ke admin
8. Admin finalisasi transfer
9. Stok tujuan bertambah

---

# 13. Kebutuhan Data Inti / Entitas Utama

## 13.1 Tenant & Organization

* Tenant
* Store/Branch
* Warehouse
* Warehouse Location
* User
* Role
* Permission

## 13.2 Master Produk

* Product Category
* Attribute Definition
* Brand
* Product
* Product Attribute Value
* Product Unit
* Unit Conversion
* Product Price
* Product QR Code

## 13.3 Pihak Terkait

* Supplier
* Customer
* Driver
* Vehicle

## 13.4 Procurement

* Purchase Order
* Purchase Order Item
* Goods Receipt
* Supplier Invoice
* Supplier Payment

## 13.5 Sales

* Sales Invoice
* Sales Invoice Item
* POS Transaction
* Payment
* Receivable

## 13.6 Delivery

* Delivery Order / Surat Jalan
* Delivery Order Item
* Delivery Status Log
* Delivery Issue
* Delivery Replacement

## 13.7 Inventory

* Inventory Balance
* Inventory Ledger
* Stock Reservation
* Stock Adjustment
* Stock Opname
* Stock Transfer
* Stock Transfer Item
* In-Transit Record

## 13.8 Finance

* Payable
* Receivable Payment
* Payable Payment
* Expense
* Tax Summary

## 13.9 Dokumen & Audit

* Print Job
* Activity Log
* Status Log
* Document Reference

---

# 14. Aturan Bisnis Utama

1. Semua data harus terikat ke tenant
2. Produk boleh memiliki atribut berbeda tergantung kategori
3. Brand bersifat opsional
4. Produk wajib memiliki satuan dasar stok
5. Satu produk dapat dibeli dari banyak supplier
6. Harga jual tidak boleh di bawah harga minimum tanpa mekanisme kontrol
7. Penjualan langsung tidak membutuhkan surat jalan
8. Penjualan kirim membutuhkan surat jalan
9. Satu invoice dapat memiliki lebih dari satu surat jalan
10. Satu surat jalan hanya mencakup item dan qty yang dialokasikan kepadanya
11. Total qty surat jalan tidak boleh melebihi qty invoice
12. Transfer gudang harus punya dokumen transfer
13. Stok tidak boleh hilang tanpa jejak mutasi
14. Pembayaran dapat final setelah dokumen pengiriman kembali
15. Jika transaksi belum lunas, sistem harus membentuk piutang
16. Jika pembelian belum lunas, sistem harus membentuk hutang
17. Barang rusak/kurang saat pengiriman harus dapat direkam sebagai issue
18. Pencetakan dari tablet harus melalui antrean print terpusat
19. Semua perubahan penting wajib terekam dalam audit trail

---

# 15. Non-Functional Requirements

## 15.1 Security

* Isolasi data tenant
* Role-based access control
* Secure authentication
* Audit log
* Session timeout
* Enkripsi data sensitif yang relevan

## 15.2 Performance

* Pencarian produk cepat
* Scan QR code responsif
* Transaksi kasir harus efisien
* Dashboard owner harus memuat data ringkas dengan cepat
* Sistem harus mampu menangani banyak transaksi paralel dari beberapa kasir

## 15.3 Reliability

* Print queue harus dapat dipantau
* Transaksi tidak boleh hilang saat koneksi fluktuatif ringan
* Dokumen harus memiliki nomor unik
* Perubahan stok harus konsisten

## 15.4 Scalability

* Siap banyak tenant
* Siap banyak toko per tenant
* Siap banyak gudang per tenant
* Siap volume produk tinggi
* Siap histori transaksi jangka panjang

## 15.5 Usability

* Admin dan kasir non-teknis harus mudah memahami UI
* Form tidak boleh terlalu teknis
* Input produk harus terstruktur jelas
* Transaksi kasir harus sesingkat mungkin

## 15.6 Maintainability

* Modular
* Mudah dikembangkan per fase
* Struktur data fleksibel untuk kategori produk baru

---

# 16. Kebutuhan UI/UX Tingkat Tinggi

## Prinsip

* Bersih
* Cepat
* Ramah non-teknis
* Minim istilah teknis
* Fokus ke operasional harian

## Hal Penting

* Search produk cepat
* Scan QR mudah
* Highlight stok, harga, dan status pembayaran
* Status surat jalan harus jelas
* Laporan owner mudah dibaca
* Form produk harus menyesuaikan kategori
* Jangan menampilkan JSON atau field teknis ke user
* Workflow cetak harus transparan

---

# 17. Dashboard dan Screen Utama

## Untuk Owner

* Dashboard ringkasan bisnis
* Laporan penjualan
* Laporan pembelian
* Laba rugi
* Piutang
* Hutang
* Pajak
* Performa cabang/gudang

## Untuk Admin

* Dashboard operasional
* Master produk
* Supplier
* Customer
* Procurement
* Inventory
* Delivery
* Transfer gudang
* Piutang
* Hutang
* Print queue
* Laporan

## Untuk Kasir

* POS screen
* Scan product
* Cart
* Customer selection
* Payment
* Print status
* Riwayat transaksi sendiri

---

# 18. KPI Produk

## Operasional

* Waktu transaksi kasir
* Akurasi stok
* Jumlah surat jalan pending
* Jumlah dokumen belum kembali
* Waktu finalisasi delivery
* Jumlah issue pengiriman

## Bisnis

* Penurunan selisih stok
* Penurunan piutang tak tertagih
* Peningkatan visibilitas laba/rugi
* Peningkatan kecepatan pembuatan laporan
* Peningkatan keteraturan proses delivery

## Sistem

* Uptime
* Keberhasilan print job
* Kecepatan pencarian produk
* Kecepatan sinkron transaksi kasir

---

# 19. Risiko Produk

1. Model data produk terlalu kaku
2. Inventory ledger tidak konsisten
3. Split shipment tidak didukung secara benar
4. Proses manual tidak sinkron dengan status sistem
5. Piutang salah karena update terlambat setelah dokumen kembali
6. Print relay di admin PC menjadi bottleneck
7. Laporan laba/rugi tidak akurat jika harga modal dan mutasi stok salah
8. Kompleksitas multi-tenant tidak ditangani dari awal
9. UI terlalu rumit untuk admin dan kasir lapangan
10. Transfer antar gudang tanpa model in-transit menyebabkan stok salah

---

# 20. Prioritas MVP / Fase Implementasi

## Fase 1 / MVP

* Multi-tenant dasar
* Role & access
* Master produk fleksibel
* Supplier & customer
* Gudang & lokasi
* Procurement dasar
* Inventory ledger
* POS penjualan langsung
* Penjualan pengiriman
* Surat jalan
* Split shipment
* Piutang/hutang dasar
* Print queue
* Dashboard dasar
* Laporan inti

## Fase 2

* Transfer antar gudang lebih lengkap
* Issue kerusakan/penggantian
* Stock opname
* Approval lanjutan
* Laporan laba/rugi lebih detail
* Pajak lebih matang

## Fase 3

* Digitalisasi proses gudang/supir
* Bukti kirim digital
* Notifikasi
* Integrasi lanjutan
* Analytics lebih dalam
* Billing SaaS tenant

---

# 21. Acceptance Criteria Tingkat Tinggi

1. Sistem dapat dipakai oleh lebih dari satu tenant tanpa data tercampur
2. Admin dapat membuat produk dengan atribut berbeda sesuai kategori
3. Kasir dapat melakukan scan produk dari tablet
4. Nota dapat dicetak melalui admin print station
5. Admin dapat membuat pembelian dan stok bertambah saat penerimaan
6. Admin dapat membuat transaksi kirim dan menghasilkan surat jalan
7. Satu invoice dapat dipecah menjadi beberapa surat jalan
8. Admin dapat mencatat status akhir berdasarkan dokumen kembali
9. Penjualan utang otomatis masuk piutang
10. Pembelian tempo otomatis masuk hutang
11. Transfer antar gudang dapat dicatat resmi
12. Owner dapat melihat laporan penjualan, stok, piutang, hutang, laba/rugi dasar, dan pajak dasar
13. Semua mutasi stok dapat ditelusuri
14. Semua perubahan penting dapat diaudit

---

# 22. Catatan Desain Penting untuk Tim Engineering

1. Jangan gunakan schema produk statis
2. Gunakan pendekatan atribut dinamis per kategori
3. Buat inventory ledger sebagai sumber kebenaran
4. Pisahkan sales walk-in dan sales delivery sebagai dua flow utama
5. Buat dokumen delivery sebagai entitas sendiri, bukan sekadar lampiran invoice
6. Support partial allocation item ke beberapa surat jalan
7. Gunakan status workflow yang jelas dan eksplisit
8. Siapkan print dispatcher / print queue service
9. Semua entitas inti wajib tenant-aware
10. Audit trail bukan fitur tambahan, tetapi kebutuhan inti

---

# 23. Ringkasan Akhir

Produk ini adalah sistem ERP vertikal khusus toko material berbasis web, multi-tenant, dan berfokus pada realitas operasional lapangan. Nilai utamanya ada pada kemampuan mengelola produk heterogen, stok multi lokasi, transaksi fleksibel, surat jalan multi tahap, pengiriman split shipment, piutang/hutang, dan pelaporan owner.

Keberhasilan sistem ini tidak bergantung pada banyaknya fitur semata, tetapi pada:

* ketepatan model produk
* akurasi inventory ledger
* kejelasan workflow dokumen
* desain multi-tenant yang aman
* UI yang cocok untuk pengguna operasional

