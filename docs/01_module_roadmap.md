# docs/01_module_roadmap.md
# Module Roadmap — Official Build Order
Last synced: 2026-02-25
Source of truth: PDF 5 (Urutan Pengerjaan Modul)

Each module is implemented in its own branch: `mod-NN-<slug>`.
Do NOT implement a future module while on the current module's branch.

---

## Module 01 — User & Access Control
**Branch:** `mod-01-access`
**Phase:** 1

### Purpose
Provide the security foundation for all other modules: authentication, role-based access control scoped per branch, and an immutable audit trail for all user actions.

### Entities / Tables
- `users`
- `roles`
- `permissions`
- `role_permissions` (pivot)
- `user_branch_access` (pivot — which branches a user can access)
- `audit_logs` (immutable, append-only)

### Invariants to Enforce
- I3 (SPEC.md): No sensitive action without an `audit_logs` entry
- A user assigned to Branch A cannot access Branch B data (except Super Admin and Owner)
- Roles: `super_admin | admin_cabang | kasir | accounting | owner` — no other values
- `audit_logs` rows are never updated or deleted by application code
- Password changes require audit log entry
- Failed login attempts are logged

### Minimal UI / API Surfaces
- `POST /login` — authenticate, return session
- `POST /logout`
- `GET/POST/PUT/DELETE /users` — Super Admin only
- `GET/POST /roles` — Super Admin only (read for others)
- `POST /users/{id}/assign-branch` — branch assignment management
- `GET /audit-logs` — Owner and Super Admin only, paginated

### Golden Tests (Given / When / Then)

**T01-1 — Admin creates a user**
- Given: Super Admin is logged in
- When: POST /users with valid payload
- Then: User created, `audit_logs` row created with `action=create`, `auditable_type=User`, `new_values` contains user data (no password)

**T01-2 — Branch scoping enforced**
- Given: Kasir is assigned to Branch A only
- When: Kasir requests a transaction list filtered to Branch B
- Then: Returns 403 Forbidden (or empty result scoped to Branch A only)

**T01-3 — Owner sees all branches**
- Given: User has role `owner`
- When: Owner requests audit logs
- Then: Returns audit logs across all branches

**T01-4 — Role constraint**
- Given: Admin tries to assign role `super_admin` to a new user
- When: Admin does not have super_admin role
- Then: 403 Forbidden

**T01-5 — Audit log immutability**
- Given: Any audit_log entry exists
- When: Any code path attempts to UPDATE or DELETE audit_logs
- Then: Operation is blocked (enforced via model-level guard or DB-level approach)

---

## Module 02 — Master Data
**Branch:** `mod-02-master-data`
**Phase:** 1

### Purpose
Provide all reference/lookup data that every operational module depends on: products, branches, warehouses, customers, suppliers, and a basic Chart of Accounts for Phase 3 readiness.

### Entities / Tables
- `branches`
- `warehouses` (per branch)
- `categories`
- `units`
- `products` (SKU, barcode, category, unit, minimum stock)
- `customers`
- `suppliers`
- `chart_of_accounts` (basic COA — seeded, minimal management UI)

### Invariants to Enforce
- `products.sku` must be globally unique (across all branches)
- `products.barcode` must be unique where not null
- A `warehouse` must always have a valid `branch_id`
- Soft-delete only — no hard delete for any record referenced by transactions
- COA `code` must be unique
- Changes to products, customers, suppliers must write `audit_logs`

### Minimal UI / API Surfaces
- Products: CRUD + search by name/SKU/barcode + pagination
- Categories: CRUD (simple tree, max 2 levels)
- Units: CRUD (simple list)
- Branches: CRUD (Super Admin only)
- Warehouses: CRUD per branch
- Customers: CRUD + search by name/phone
- Suppliers: CRUD + search
- COA: Seed on install; basic list view (no full management UI needed in Phase 1)

### Golden Tests

**T02-1 — Duplicate SKU rejected**
- Given: Product with SKU "MAT-001" exists
- When: New product submitted with SKU "MAT-001"
- Then: Validation error "SKU already exists"

**T02-2 — Soft-delete product**
- Given: Product is soft-deleted (deleted_at set)
- When: POS barcode scan for that product
- Then: Product not found (excluded from active product queries)

**T02-3 — Warehouse branch constraint**
- Given: Warehouse form submitted without branch_id
- Then: Validation error

**T02-4 — Barcode uniqueness**
- Given: Two products submitted with the same barcode
- Then: Second save rejected with uniqueness error

**T02-5 — Audit on product change**
- Given: Admin changes product name
- When: PUT /products/{id}
- Then: `audit_logs` entry created with old_values and new_values

---

## Module 03 — Pricing Management
**Branch:** `mod-03-pricing`
**Phase:** 1

### Purpose
Manage product selling prices: global default prices and per-branch overrides, with a full immutable change history. Prices must be correct before POS goes live.

### Entities / Tables
- `product_prices` (global default — one row per product)
- `branch_product_prices` (per-branch override — one row per product per branch)
- `price_change_logs` (immutable history of all price changes)

### Invariants to Enforce
- Every price change (global or branch) must create a `price_change_logs` entry with `old_price`, `new_price`, `changed_by`, `changed_at`
- Branch price override requires explicit permission (`permissions: prices.override_branch`)
- Bulk price update is queued as a job (not processed in-request) — Hostinger safe
- POS reads: branch override if exists, else global price; never null

### Minimal UI / API Surfaces
- `GET /products/{id}/price` — returns effective price for a given branch
- `PUT /products/{id}/price` — update global price (Super Admin / Owner)
- `PUT /branches/{branch_id}/products/{id}/price` — branch override (Admin Cabang with permission)
- `POST /prices/bulk-update` — enqueues bulk price update job
- `GET /products/{id}/price-history` — paginated change log

### Golden Tests

**T03-1 — Global price fallback**
- Given: Product has global price = 50000; Branch A has no override
- When: POS requests price for this product at Branch A
- Then: Returns 50000 (global price)

**T03-2 — Branch override takes precedence**
- Given: Product has global price = 50000; Branch A override = 45000
- When: POS requests price for this product at Branch A
- Then: Returns 45000

**T03-3 — Price change logged**
- Given: Global price = 50000
- When: Admin updates global price to 55000
- Then: `price_change_logs` row created with old_price=50000, new_price=55000, changed_by=admin_id

**T03-4 — Bulk update queued**
- Given: 200 products selected for bulk price update (+10%)
- When: POST /prices/bulk-update submitted
- Then: Job dispatched to queue; response confirms "queued"; each product price change is logged individually

**T03-5 — Unauthorized branch override rejected**
- Given: Kasir attempts to set branch price override
- When: PUT /branches/{id}/products/{id}/price
- Then: 403 Forbidden

---

## Module 04 — Inventory Management (Core Engine)
**Branch:** `mod-04-inventory`
**Phase:** 1

### Purpose
The stock engine: maintain real-time stock balances per branch, enforce deduction timing rules (Ambil vs Kirim), and write every movement to the immutable stock ledger. All other modules call the inventory engine; it never calls them.

### Entities / Tables
- `stock_balances` (current qty per product per branch — derived summary)
- `stock_ledger` (immutable, append-only — every movement ever)

### Invariants to Enforce
- **I1 (SPEC.md):** Every stock change → `stock_ledger` entry. No exceptions.
- `stock_balances.qty_on_hand` must always equal `SUM(stock_ledger.qty_change)` for that product+branch
- Ambil Langsung: deduct on transaction save
- Kirim: deduct ONLY on DO→InDelivery transition
- Return: add on Return processed
- Stock cannot go below 0 without an explicit adjustment (system should warn, not silently allow negative unless explicitly permitted by Super Admin setting)
- Adjustment requires mandatory reason field + audit log

### Minimal UI / API Surfaces
- `GET /branches/{id}/stock` — stock balance list for a branch (filterable, searchable)
- `GET /products/{id}/stock-ledger` — movement history for a product (paginated)
- `POST /stock/adjustment` — manual adjustment (with reason; creates ledger entry + audit log)
- `GET /stock/low-stock-alerts` — products at or below minimum_stock per branch
- Stock opname: schema ready (Phase 2 UI)

### Golden Tests

**T04-1 — Ambil Langsung deducts on save**
- Given: Product X at Branch A has qty=10
- When: Ambil Langsung transaction saved for qty=3
- Then: `stock_balances.qty_on_hand` = 7; `stock_ledger` has entry: qty_change=-3, qty_before=10, qty_after=7, movement_type=sale_ambil

**T04-2 — Kirim does NOT deduct on transaction save**
- Given: Product X at Branch A has qty=10
- When: Kirim transaction saved for qty=3; DO created in Draft
- Then: `stock_balances.qty_on_hand` still = 10; no stock_ledger entry for this transaction yet

**T04-3 — Kirim deducts on InDelivery**
- Given: Kirim transaction with DO in Released status; qty=3; stock=10
- When: Admin transitions DO → InDelivery
- Then: `stock_balances.qty_on_hand` = 7; `stock_ledger` entry: movement_type=sale_kirim_out, qty_change=-3

**T04-4 — Return restores stock**
- Given: DO marked Failed; Return document created for qty=3
- When: Return processed
- Then: `stock_balances.qty_on_hand` += 3; `stock_ledger` entry: movement_type=return_in, qty_change=+3

**T04-5 — Adjustment requires reason**
- Given: Admin tries to adjust stock without reason field
- Then: Validation error "Reason is required"
- When: Reason provided and saved
- Then: `stock_ledger` entry + `audit_logs` entry both created

---

## Module 05 — Sales Transaction (POS)
**Branch:** `mod-05-pos`
**Phase:** 1

### Purpose
Core POS module for kasir operations on tablet. Creates transactions in two modes (Ambil Langsung / Kirim), stores financial snapshots at creation, and triggers downstream effects (stock deduction for Ambil, DO creation for Kirim, AR creation for Credit).

### Entities / Tables
- `transactions` (header)
- `transaction_lines` (per-item; stores all snapshots)

### Invariants to Enforce
- `hpp_snapshot`, `unit_price_snapshot`, `margin_snapshot` are **REQUIRED** and non-null on every transaction_line
- `transaction_mode` must be `ambil` or `kirim`
- `payment_status` must be `paid` (cash/transfer) or `credit`
- Credit transactions REQUIRE: customer_id or (customer_name + phone + due_date)
- Kirim transactions REQUIRE: delivery_address, recipient_name, recipient_phone
- After save (Ambil): stock deducted immediately via Inventory Engine
- After save (Kirim): DO created in Draft, NO stock deduction
- Transaction lines are immutable after DO Draft is created
- `transaction_no` is system-generated, unique, sequential per branch per day (e.g. TRX-A01-20260225-0001)

### Minimal UI / API Surfaces
- POS screen: product search (name/SKU/barcode), cart management, payment selection, notes
- `POST /transactions` — create transaction (atomic: transaction + lines + stock/DO + AR if credit)
- `GET /transactions` — list with filters (branch, date, status, mode)
- `GET /transactions/{id}` — detail
- `GET /transactions/{id}/receipt` — printable receipt/nota
- Void: `POST /transactions/{id}/void` — only if no DO or DO still in Draft; creates audit log

### Golden Tests (from PDF 3 — Skenario A, B, C, D)

**T05-A — Ambil Langsung Lunas (Skenario A)**
- Given: Product X (price=50000, hpp=30000, stock=20) at Branch A
- When: Kasir saves Ambil Langsung transaction, qty=2, payment=cash
- Then:
  - `transactions.payment_status` = `paid`
  - `transactions.transaction_mode` = `ambil`
  - `transaction_lines`: unit_price_snapshot=50000, hpp_snapshot=30000, margin_snapshot=20000
  - `stock_balances.qty_on_hand` = 18
  - `stock_ledger` entry created (movement_type=sale_ambil, qty_change=-2)
  - No delivery_order created
  - Receipt printable

**T05-B — Ambil Langsung Kredit (Skenario B)**
- Given: Product X (price=50000, stock=20) at Branch A
- When: Kasir saves Ambil Langsung transaction, qty=1, payment=credit, customer_name="Budi", phone="08111", due_date="2026-03-25"
- Then:
  - `transactions.payment_status` = `credit`
  - `receivables` row created: total_amount=50000, outstanding_amount=50000, due_date=2026-03-25
  - Stock deducted immediately (Ambil mode)
  - Credit nota printable

**T05-B-fail — Credit without due_date rejected**
- Given: Kasir submits credit transaction without due_date
- Then: Validation error "Tanggal jatuh tempo wajib diisi untuk transaksi kredit"

**T05-C — Kirim Lunas (Skenario C)**
- Given: Product X (stock=20) at Branch A
- When: Kasir saves Kirim transaction, qty=3, payment=cash, delivery_address="Jl. Merdeka 1", recipient="Andi"
- Then:
  - `transactions.payment_status` = `paid`
  - `transactions.transaction_mode` = `kirim`
  - `delivery_orders` row created: fulfillment_status=`draft`
  - Stock NOT deducted (still=20)
  - DO and nota printable

**T05-D — Kirim Kredit (Skenario D)**
- Given: Product X (stock=20) at Branch A
- When: Kasir saves Kirim transaction, qty=3, payment=credit, all required fields present
- Then:
  - `transactions.payment_status` = `credit`
  - `delivery_orders` row created: fulfillment_status=`draft`
  - `receivables` row created
  - Stock NOT deducted (still=20)

**T05-E — Snapshot immutability after DO Draft**
- Given: Kirim transaction saved (DO in Draft)
- When: Any attempt to update `transaction_lines`
- Then: Rejected with error "Transaction lines are locked (DO exists)"

---

## Module 06 — Delivery Order (DO)
**Branch:** `mod-06-delivery`
**Phase:** 1

### Purpose
Control the full fulfillment lifecycle for Kirim transactions. Enforce stock deduction only at InDelivery, lock transaction at Released, and handle failed delivery via formal Return process.

### Entities / Tables
- `delivery_orders`
- `delivery_order_lines` (snapshot of transaction_lines at DO creation)
- `returns`
- `return_lines`

### Invariants to Enforce
- DO only created from Kirim transactions (never Ambil)
- FulfillmentStatus transitions are strictly enforced (no skipping)
- Stock deducted ONLY on `Draft/Released → InDelivery` transition
- After DO Released: parent transaction is fully immutable
- Return document required for Failed→Returned; stock added on Return processed
- All DO status transitions must write `audit_logs` entry

### Minimal UI / API Surfaces
- `GET /delivery-orders` — list with filters (branch, status, date)
- `GET /delivery-orders/{id}` — detail with lines
- `POST /delivery-orders/{id}/release` — Draft → Released
- `POST /delivery-orders/{id}/in-delivery` — Released → InDelivery (triggers stock deduction)
- `POST /delivery-orders/{id}/delivered` — InDelivery → Delivered
- `POST /delivery-orders/{id}/failed` — InDelivery → Failed
- `POST /delivery-orders/{id}/return` — Failed → Returned (creates Return document)
- `GET /delivery-orders/{id}/print` — printable DO document

### Golden Tests

**T06-1 — Release DO**
- Given: DO in Draft status
- When: Admin POST /delivery-orders/{id}/release
- Then: DO status = `released`; `audit_logs` entry created; stock NOT deducted

**T06-2 — InDelivery triggers stock deduction**
- Given: DO in Released status; Product X qty=5 in stock; DO qty=3
- When: Admin POST /delivery-orders/{id}/in-delivery
- Then: DO status = `in_delivery`; `stock_balances.qty_on_hand` = 2; `stock_ledger` entry (movement_type=sale_kirim_out, qty_change=-3)

**T06-3 — Delivered marks DO complete**
- Given: DO in InDelivery status
- When: Admin POST /delivery-orders/{id}/delivered
- Then: DO status = `delivered`; `delivered_at` timestamp set; audit log created

**T06-4 — Failed delivery path**
- Given: DO in InDelivery status
- When: Admin POST /delivery-orders/{id}/failed
- Then: DO status = `failed`; `failed_at` set; audit log created; stock NOT restored yet

**T06-5 — Return after failed restores stock**
- Given: DO in Failed status; qty=3 was deducted at InDelivery
- When: Admin processes Return for all 3 qty
- Then: DO status = `returned`; `stock_balances.qty_on_hand` += 3; `stock_ledger` entry (movement_type=return_in, qty_change=+3); `returns` document created

**T06-6 — Transaction immutable after Released**
- Given: DO status = released (or later)
- When: Any attempt to modify transaction_lines
- Then: Rejected with error message; audit_log entry for attempted violation

**T06-7 — Invalid status transition rejected**
- Given: DO in Draft status
- When: Attempt to transition directly to Delivered (skipping Released and InDelivery)
- Then: Rejected with error "Invalid status transition"

---

## Module 07 — Account Receivable (Piutang)
**Branch:** `mod-07-ar`
**Phase:** 1

### Purpose
Track and manage all customer credit obligations. AR is auto-created from Credit transactions. Supports partial payments, due date monitoring, and aging reports to control credit risk.

### Entities / Tables
- `receivables`
- `receivable_payments`

### Invariants to Enforce
- AR auto-created atomically with Credit transaction (same DB transaction)
- `receivables.outstanding_amount` = `total_amount` - `SUM(receivable_payments.amount_paid)` always
- PaymentStatus transitions: `credit → partially_paid → paid` (via `transactions.payment_status`)
- `receivable_payments` rows are immutable (append-only; no correction — use adjustment)
- Partial payment: updates outstanding_amount; sets transaction PaymentStatus = `partially_paid`
- Full payment: sets transaction PaymentStatus = `paid`; outstanding_amount = 0

### Minimal UI / API Surfaces
- `GET /receivables` — list with filters (branch, customer, status, due_date range)
- `GET /receivables/{id}` — detail with payment history
- `POST /receivables/{id}/pay` — record a payment (partial or full)
- `GET /receivables/aging` — aging report (0-30 / 31-60 / 61-90 / >90 days buckets)
- `GET /customers/{id}/receivables` — all outstanding AR for a customer

### Golden Tests

**T07-1 — AR auto-created on Credit transaction**
- Given: Credit transaction saved with amount=1500000, due_date=2026-03-25
- Then: `receivables` row created: total_amount=1500000, outstanding_amount=1500000, status=unpaid, due_date=2026-03-25

**T07-2 — Partial payment**
- Given: Receivable total=1500000, outstanding=1500000
- When: Payment of 500000 recorded
- Then: outstanding_amount=1000000; transactions.payment_status=`partially_paid`; `receivable_payments` row created

**T07-3 — Full payment**
- Given: Receivable outstanding=500000
- When: Payment of 500000 recorded
- Then: outstanding_amount=0; transactions.payment_status=`paid`; receivables.status=`paid`

**T07-4 — Overpayment rejected**
- Given: Receivable outstanding=200000
- When: Payment of 300000 submitted
- Then: Validation error "Pembayaran melebihi sisa piutang"

**T07-5 — Aging report accuracy**
- Given: Receivables with due_dates: 5 days ago, 35 days ago, 65 days ago, 100 days ago
- When: GET /receivables/aging (as of today)
- Then: Each receivable appears in the correct bucket (0-30: 1, 31-60: 1, 61-90: 1, >90: 1)

---

## Module 08 — Purchasing & Account Payable
**Branch:** `mod-08-purchasing`
**Phase:** 2

### Purpose
Manage incoming stock from suppliers. Goods receipt increases stock and updates HPP (moving average). Credit purchases auto-create AP (utang supplier) with payment tracking.

### Entities / Tables
- `purchase_orders`
- `purchase_order_lines`
- `payables`
- `payable_payments`

### Invariants to Enforce
- Stock increases on goods receipt confirmation (formal process, not on PO creation)
- `purchase_order_lines.unit_cost` feeds HPP moving average calculation: new_hpp = (old_qty × old_hpp + received_qty × unit_cost) / (old_qty + received_qty)
- AP auto-created for credit purchases
- `payable_payments` are immutable (append-only)
- All receipts write `stock_ledger` entry (movement_type=purchase_in)
- HPP recalculation is forward-only (no retroactive impact)

### Minimal UI / API Surfaces
- `POST /purchase-orders` — create PO
- `POST /purchase-orders/{id}/receive` — confirm goods receipt (triggers stock increase + HPP update)
- `GET /payables` — list AP with filters
- `POST /payables/{id}/pay` — record payment
- `GET /payables/aging` — AP aging report

### Golden Tests

**T08-1 — Goods receipt increases stock**
- Given: Product X qty=5 in Branch A
- When: Purchase of qty=10 received at unit_cost=25000
- Then: stock_balances += 10; stock_ledger entry (movement_type=purchase_in, qty_change=+10)

**T08-2 — HPP moving average updated**
- Given: Product X has hpp_current=30000, qty_on_hand=5
- When: Receive 10 units at unit_cost=25000
- Then: new_hpp = (5×30000 + 10×25000) / 15 = 26666.67; products.hpp_current updated

**T08-3 — AP auto-created for credit purchase**
- Given: Purchase received, payment_type=credit
- Then: `payables` row created with total_amount = sum of line totals

---

## Module 09 — Accounting (Basic Integrated)
**Branch:** `mod-09-accounting`
**Phase:** 3

### Purpose
Generate accounting journals automatically from all transaction events stored in Phase 1-2. Produce basic financial reports: General Ledger, Trial Balance, P&L, Simple Balance Sheet, Simple Cash Flow.

### Entities / Tables
- `journal_entries` (header: source_type, source_id, entry_date, description)
- `journal_lines` (journal_id, account_id, debit_amount, credit_amount)

### Invariants to Enforce
- Journals generated FROM events/snapshots; NEVER from direct history editing
- No retroactive modification of posted journals
- Phase 1 data (hpp_snapshot, price_snapshot, discount, payment_method, branch) is sufficient — no re-collection needed
- No multi-ledger, no deferred revenue, no complex accrual engine
- Double-entry balance: SUM(debit) = SUM(credit) per journal_entry

### Minimal UI / API Surfaces
- `GET /accounting/general-ledger` — by account, date range
- `GET /accounting/trial-balance` — as of date
- `GET /accounting/profit-loss` — by period (monthly/annual)
- `GET /accounting/balance-sheet` — as of date
- `GET /accounting/cash-flow` — simplified, by period

### Journal Templates (canonical)

| Event | Debit | Credit |
|---|---|---|
| Ambil Langsung Paid | Kas/Bank + HPP | Penjualan + Persediaan |
| Ambil Langsung Credit | Piutang + HPP | Penjualan + Persediaan |
| AR Payment received | Kas/Bank | Piutang |
| Kirim Paid (on InDelivery) | HPP | Persediaan |
| Purchase received (cash) | Persediaan | Kas/Bank |
| Purchase received (credit) | Persediaan | Utang Dagang |
| AP Payment | Utang Dagang | Kas/Bank |
| Return (stock back) | Persediaan | HPP |

### Golden Tests

**T09-1 — Cash sale journal**
- Given: Ambil Langsung Paid, amount=100000, HPP=60000
- Then: journal_entry created; journal_lines: Dr Kas 100000, Cr Penjualan 100000, Dr HPP 60000, Cr Persediaan 60000

**T09-2 — Balance check**
- Given: Any journal_entry
- Then: SUM(debit_amount) = SUM(credit_amount) on journal_lines

---

## Module 10 — Reporting & Dashboard
**Branch:** `mod-10-reporting`
**Phase:** 2 (basic) + Phase 3 (full financial)

### Purpose
Provide owner-level visibility: real-time KPIs, periodic sales recaps, and exportable reports. All data derived from existing tables — no new operational tables.

### Entities / Tables
(No new tables — queries against all existing tables)

### Invariants to Enforce
- Reports use snapshot data (immutable) — no recalculation
- All reports filterable by: date range, branch, payment method, transaction status
- Export to CSV/XLS must work within PHP execution time limits (chunk large datasets via queue if needed)
- Period reports do not change after the period closes (stable history)

### Minimal UI / API Surfaces
- `GET /dashboard` — Owner KPI widget (sales today, margin, critical stock count, overdue AR, cash estimate per branch)
- `GET /reports/sales` — daily/monthly/annual recap with drill-down
- `GET /reports/products` — sales by product (top sellers, margin)
- `GET /reports/ar-aging` — AR aging summary
- `GET /reports/export` — CSV/XLS export (queued for large datasets)

### Golden Tests

**T10-1 — Dashboard sales widget**
- Given: 5 transactions today at Branch A with net total=2500000
- When: Owner views dashboard filtered to Branch A, today
- Then: Sales widget shows 2500000

**T10-2 — Monthly report stability**
- Given: January transactions are all saved
- When: Monthly report for January is generated on February 1
- Then: Same totals as on January 31 (snapshots, not live recalculation)

**T10-3 — Critical stock alert**
- Given: Product X minimum_stock=10; qty_on_hand=3
- When: Dashboard loads
- Then: Product X appears in critical stock list

**T10-4 — Export within time limit**
- Given: 5000 transaction records for export
- When: Export requested
- Then: Job queued; user notified when ready (no request timeout)

---

## Summary: Official Build Order

| # | Module | Branch | Phase |
|---|---|---|---|
| 01 | User & Access Control | `mod-01-access` | 1 |
| 02 | Master Data | `mod-02-master-data` | 1 |
| 03 | Pricing Management | `mod-03-pricing` | 1 |
| 04 | Inventory Management (Core Engine) | `mod-04-inventory` | 1 |
| 05 | Sales Transaction (POS) | `mod-05-pos` | 1 |
| 06 | Delivery Order (DO) | `mod-06-delivery` | 1 |
| 07 | Account Receivable (Piutang) | `mod-07-ar` | 1 |
| 08 | Purchasing & Account Payable | `mod-08-purchasing` | 2 |
| 09 | Accounting (Basic Integrated) | `mod-09-accounting` | 3 |
| 10 | Reporting & Dashboard | `mod-10-reporting` | 2+3 |

**Rule:** Never implement module N+1 while on branch for module N.
After each module is complete and tested, create the next branch.
