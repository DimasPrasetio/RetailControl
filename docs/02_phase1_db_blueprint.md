# docs/02_phase1_db_blueprint.md
# Phase 1 Database Blueprint
Last synced: 2026-02-25
Scope: Phase 1 modules only (01–07), future-proofed for Phase 3 accounting.

---

## Design Principles

1. **Single MySQL database** — Single Source of Truth (AGENTS.md §3)
2. **InnoDB engine** on all tables — required for foreign keys and transactions
3. **DECIMAL(15,2)** for all monetary values — never FLOAT
4. **All timestamps UTC** — Laravel's `timestamps()` stores UTC; app reads in local TZ
5. **Immutable ledgers** — `stock_ledger` and `audit_logs` are append-only; no UPDATE/DELETE
6. **Snapshot columns** — `transaction_lines` stores price/HPP at creation; never derived at query time
7. **Soft deletes** (`deleted_at`) on all master data — no hard delete of referenced records
8. **BIGINT unsigned auto-increment** for all primary keys
9. **ENUM** for status/type columns — enforced at DB level, less prone to typos than VARCHAR
10. **MySQL 8.0+ assumed** — JSON columns used for `audit_logs.old_values/new_values`

---

## Table List — Phase 1

### Group A: Auth & Access
1. `users`
2. `roles`
3. `permissions`
4. `role_permissions`
5. `user_branch_access`
6. `audit_logs`

### Group B: Master Data
7. `branches`
8. `warehouses`
9. `categories`
10. `units`
11. `products`
12. `customers`
13. `suppliers` *(Phase 1 stub — minimal columns; full AP usage in Phase 2)*
14. `chart_of_accounts` *(seeded; minimal UI — Phase 3 active usage)*

### Group C: Pricing
15. `product_prices`
16. `branch_product_prices`
17. `price_change_logs`

### Group D: Inventory Engine
18. `stock_balances`
19. `stock_ledger`

### Group E: Sales (POS)
20. `transactions`
21. `transaction_lines`

### Group F: Delivery Order
22. `delivery_orders`
23. `delivery_order_lines`
24. `returns`
25. `return_lines`

### Group G: Account Receivable
26. `receivables`
27. `receivable_payments`

### Group H: Laravel Infrastructure
28. `jobs` *(Laravel queue — database driver)*
29. `failed_jobs`
30. `sessions` *(if using database sessions)*
31. `personal_access_tokens` *(Laravel Sanctum)*

---

## Table Schemas (Key Columns Only)

---

### `users`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(100) NOT NULL
email           VARCHAR(150) NOT NULL UNIQUE
password        VARCHAR(255) NOT NULL
role_id         BIGINT UNSIGNED NOT NULL FK→roles.id
is_active       BOOLEAN DEFAULT TRUE
remember_token  VARCHAR(100) NULL
created_at, updated_at, deleted_at
```
Index: `email` (UNIQUE), `role_id`

---

### `roles`
```
id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name        ENUM('super_admin','admin_cabang','kasir','accounting','owner') NOT NULL UNIQUE
description VARCHAR(255) NULL
created_at, updated_at
```
Seeded at install. No user-created roles.

---

### `permissions`
```
id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name        VARCHAR(100) NOT NULL
slug        VARCHAR(100) NOT NULL UNIQUE   -- e.g. transactions.create, prices.override_branch
created_at, updated_at
```
Seeded. Slugs are checked in policy/gate classes.

---

### `role_permissions`
```
role_id         BIGINT UNSIGNED NOT NULL FK→roles.id
permission_id   BIGINT UNSIGNED NOT NULL FK→permissions.id
PRIMARY KEY (role_id, permission_id)
```

---

### `user_branch_access`
```
user_id         BIGINT UNSIGNED NOT NULL FK→users.id
branch_id       BIGINT UNSIGNED NOT NULL FK→branches.id
PRIMARY KEY (user_id, branch_id)
```
Note: `super_admin` and `owner` bypass this table (global access enforced in policy).

---

### `audit_logs`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id         BIGINT UNSIGNED NULL FK→users.id  -- NULL for system actions
action          ENUM('create','update','delete','status_change','void','login','logout') NOT NULL
auditable_type  VARCHAR(100) NOT NULL   -- e.g. 'Transaction', 'DeliveryOrder'
auditable_id    BIGINT UNSIGNED NOT NULL
old_values      JSON NULL
new_values      JSON NULL
ip_address      VARCHAR(45) NULL        -- supports IPv6
user_agent      VARCHAR(255) NULL
created_at      TIMESTAMP NOT NULL      -- NO updated_at — immutable
```
**CRITICAL:** No `updated_at`. No application-level UPDATE or DELETE ever issued on this table.
Index: `(auditable_type, auditable_id)`, `(user_id, created_at)`, `(created_at)` for range queries.

---

### `branches`
```
id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name        VARCHAR(100) NOT NULL
code        VARCHAR(20) NOT NULL UNIQUE   -- e.g. A01, B02
address     TEXT NULL
phone       VARCHAR(30) NULL
is_active   BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
```

---

### `warehouses`
```
id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
branch_id   BIGINT UNSIGNED NOT NULL FK→branches.id
name        VARCHAR(100) NOT NULL
is_default  BOOLEAN DEFAULT FALSE
is_active   BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
```
Index: `(branch_id)`. Unique: one `is_default=TRUE` per branch (enforced at app level).

---

### `categories`
```
id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name        VARCHAR(100) NOT NULL
parent_id   BIGINT UNSIGNED NULL FK→categories.id   -- max 2 levels
created_at, updated_at, deleted_at
```
Index: `(parent_id)`.

---

### `units`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(50) NOT NULL      -- e.g. "Karung", "Batang", "Meter"
abbreviation    VARCHAR(10) NOT NULL      -- e.g. "kg", "btg", "m"
created_at, updated_at
```

---

### `products`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
sku             VARCHAR(50) NOT NULL UNIQUE
barcode         VARCHAR(100) NULL
name            VARCHAR(200) NOT NULL
category_id     BIGINT UNSIGNED NULL FK→categories.id
unit_id         BIGINT UNSIGNED NOT NULL FK→units.id
minimum_stock   DECIMAL(15,3) DEFAULT 0   -- using 3 decimals for qty (e.g. 0.5 bag)
hpp_current     DECIMAL(15,2) DEFAULT 0   -- current moving-average HPP; updated on purchase
is_active       BOOLEAN DEFAULT TRUE
notes           TEXT NULL
created_at, updated_at, deleted_at
```
Index: `sku` (UNIQUE), `barcode` (INDEX — not unique; some products share barcode packs),
`(category_id)`, `(is_active, deleted_at)` for active product queries.

Note: `hpp_current` stores the running moving average. It is the **input** for `hpp_snapshot` at transaction creation. Never recalculate backwards.

---

### `customers`
```
id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name        VARCHAR(150) NOT NULL
phone       VARCHAR(30) NULL
address     TEXT NULL
notes       TEXT NULL
is_active   BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
```
Index: `(name)`, `(phone)`.

---

### `suppliers`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(150) NOT NULL
phone           VARCHAR(30) NULL
address         TEXT NULL
contact_person  VARCHAR(100) NULL
notes           TEXT NULL
is_active       BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
```
*(Full AP usage in Phase 2; schema complete in Phase 1 to avoid migration churn)*

---

### `chart_of_accounts`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code            VARCHAR(20) NOT NULL UNIQUE    -- e.g. 1100, 1200
name            VARCHAR(100) NOT NULL
account_type    ENUM('asset','liability','equity','revenue','expense') NOT NULL
is_system       BOOLEAN DEFAULT FALSE          -- system accounts cannot be deleted
is_active       BOOLEAN DEFAULT TRUE
created_at, updated_at
```
**Seeded at install.** Minimum required accounts:

| Code | Name | Type |
|---|---|---|
| 1100 | Kas | asset |
| 1200 | Bank | asset |
| 1300 | Piutang Dagang | asset |
| 1400 | Persediaan Barang | asset |
| 2100 | Utang Dagang | liability |
| 3100 | Modal | equity |
| 4100 | Penjualan | revenue |
| 4200 | Diskon Penjualan | revenue (contra) |
| 5100 | Harga Pokok Penjualan (HPP) | expense |

---

### `product_prices`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
product_id      BIGINT UNSIGNED NOT NULL UNIQUE FK→products.id
price           DECIMAL(15,2) NOT NULL
effective_date  DATE NOT NULL
created_at, updated_at
```

---

### `branch_product_prices`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
branch_id       BIGINT UNSIGNED NOT NULL FK→branches.id
product_id      BIGINT UNSIGNED NOT NULL FK→products.id
price           DECIMAL(15,2) NOT NULL
effective_date  DATE NOT NULL
created_at, updated_at
UNIQUE KEY uq_branch_product (branch_id, product_id)
```

---

### `price_change_logs`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
product_id      BIGINT UNSIGNED NOT NULL FK→products.id
branch_id       BIGINT UNSIGNED NULL FK→branches.id    -- NULL = global price change
old_price       DECIMAL(15,2) NOT NULL
new_price       DECIMAL(15,2) NOT NULL
changed_by      BIGINT UNSIGNED NOT NULL FK→users.id
notes           VARCHAR(255) NULL
created_at      TIMESTAMP NOT NULL   -- NO updated_at — immutable log
```
Index: `(product_id, created_at)`.

---

### `stock_balances`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
product_id      BIGINT UNSIGNED NOT NULL FK→products.id
branch_id       BIGINT UNSIGNED NOT NULL FK→branches.id
qty_on_hand     DECIMAL(15,3) NOT NULL DEFAULT 0
updated_at      TIMESTAMP NOT NULL
UNIQUE KEY uq_product_branch (product_id, branch_id)
```
This is a **denormalized summary** for fast balance lookups. Always kept in sync by the inventory engine. The authoritative source is `stock_ledger` (can be recomputed from it).

---

### `stock_ledger`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
product_id      BIGINT UNSIGNED NOT NULL FK→products.id
branch_id       BIGINT UNSIGNED NOT NULL FK→branches.id
movement_type   ENUM(
                  'sale_ambil',        -- Ambil Langsung deduction
                  'sale_kirim_out',    -- Kirim deduction (on InDelivery)
                  'purchase_in',       -- Purchase goods received
                  'return_in',         -- Return from customer / failed delivery
                  'return_out',        -- Return to supplier (Phase 2)
                  'adjustment_in',     -- Manual positive adjustment
                  'adjustment_out',    -- Manual negative adjustment
                  'transfer_in',       -- Inter-branch transfer in (Phase 2)
                  'transfer_out',      -- Inter-branch transfer out (Phase 2)
                  'opname_in',         -- Stock opname positive diff (Phase 2)
                  'opname_out'         -- Stock opname negative diff (Phase 2)
                ) NOT NULL
source_type     VARCHAR(100) NOT NULL    -- e.g. 'Transaction', 'DeliveryOrder', 'Return', 'StockAdjustment'
source_id       BIGINT UNSIGNED NOT NULL  -- FK to the source record
qty_change      DECIMAL(15,3) NOT NULL    -- positive = in, negative = out
qty_before      DECIMAL(15,3) NOT NULL    -- balance before this movement
qty_after       DECIMAL(15,3) NOT NULL    -- balance after (= qty_before + qty_change)
hpp_at_movement DECIMAL(15,2) NULL        -- HPP at time of movement (for cost tracking)
notes           VARCHAR(255) NULL
created_by      BIGINT UNSIGNED NULL FK→users.id
created_at      TIMESTAMP NOT NULL        -- NO updated_at — immutable
```
**CRITICAL:** Append-only. No UPDATE or DELETE ever issued on this table.
Index: `(product_id, branch_id, created_at)` — primary query pattern for ledger history.
Index: `(source_type, source_id)` — reverse lookup from source record.
Index: `(created_at)` — for period-based reports.

---

### `transactions`
```
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
transaction_no      VARCHAR(30) NOT NULL UNIQUE   -- e.g. TRX-A01-20260225-0001
branch_id           BIGINT UNSIGNED NOT NULL FK→branches.id
customer_id         BIGINT UNSIGNED NULL FK→customers.id   -- required for credit
transaction_mode    ENUM('ambil','kirim') NOT NULL
payment_method      ENUM('cash','transfer','credit') NOT NULL
payment_status      ENUM('paid','credit','partially_paid','cancelled') NOT NULL
transaction_date    DATE NOT NULL
gross_amount        DECIMAL(15,2) NOT NULL    -- sum of (unit_price_snapshot × qty) per line
discount_amount     DECIMAL(15,2) NOT NULL DEFAULT 0
net_amount          DECIMAL(15,2) NOT NULL    -- gross_amount - discount_amount
notes               TEXT NULL
-- Credit-specific (required if payment_method=credit)
buyer_name          VARCHAR(150) NULL
buyer_phone         VARCHAR(30) NULL
due_date            DATE NULL
-- Kirim-specific (required if transaction_mode=kirim)
delivery_address    TEXT NULL
recipient_name      VARCHAR(150) NULL
recipient_phone     VARCHAR(30) NULL
-- Audit
created_by          BIGINT UNSIGNED NOT NULL FK→users.id
updated_by          BIGINT UNSIGNED NULL FK→users.id
created_at, updated_at
```
Index: `(branch_id, transaction_date)`, `(payment_status)`, `(transaction_mode)`, `(customer_id)`.

Note: `buyer_name/buyer_phone` are denormalized for cases where no `customer_id` is selected (walk-in credit customer). If `customer_id` is set, these can be derived from customers table but are still stored for snapshot stability.

---

### `transaction_lines`
```
id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
transaction_id          BIGINT UNSIGNED NOT NULL FK→transactions.id
product_id              BIGINT UNSIGNED NOT NULL FK→products.id
qty                     DECIMAL(15,3) NOT NULL
unit_price_snapshot     DECIMAL(15,2) NOT NULL    -- price at transaction creation
hpp_snapshot            DECIMAL(15,2) NOT NULL    -- HPP at transaction creation
margin_snapshot         DECIMAL(15,2) NOT NULL    -- unit_price_snapshot - hpp_snapshot
discount_line           DECIMAL(15,2) NOT NULL DEFAULT 0
subtotal                DECIMAL(15,2) NOT NULL    -- (unit_price_snapshot - discount_line) × qty
created_at              TIMESTAMP NOT NULL        -- NO updated_at — effectively immutable after DO Draft
```
**DESIGN NOTE:** No `updated_at` signals that these rows should not be updated post-creation. The application enforces this via policy checks. If a correction is needed pre-DO-Draft, the transaction is voided and re-entered.

Index: `(transaction_id)`.

---

### `delivery_orders`
```
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
do_no               VARCHAR(30) NOT NULL UNIQUE    -- e.g. DO-A01-20260225-0001
transaction_id      BIGINT UNSIGNED NOT NULL UNIQUE FK→transactions.id
branch_id           BIGINT UNSIGNED NOT NULL FK→branches.id
fulfillment_status  ENUM('draft','released','in_delivery','delivered','failed','returned') NOT NULL DEFAULT 'draft'
delivery_address    TEXT NOT NULL
recipient_name      VARCHAR(150) NOT NULL
recipient_phone     VARCHAR(30) NOT NULL
notes               TEXT NULL
-- Timestamps for each transition
released_at         TIMESTAMP NULL
in_delivery_at      TIMESTAMP NULL
delivered_at        TIMESTAMP NULL
failed_at           TIMESTAMP NULL
returned_at         TIMESTAMP NULL
-- Audit
created_by          BIGINT UNSIGNED NOT NULL FK→users.id
updated_by          BIGINT UNSIGNED NULL FK→users.id
created_at, updated_at
```
Index: `(transaction_id)` (UNIQUE), `(branch_id, fulfillment_status)`, `(fulfillment_status, created_at)`.

---

### `delivery_order_lines`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
do_id           BIGINT UNSIGNED NOT NULL FK→delivery_orders.id
product_id      BIGINT UNSIGNED NOT NULL FK→products.id
qty             DECIMAL(15,3) NOT NULL    -- snapshot from transaction_lines at DO creation
created_at      TIMESTAMP NOT NULL        -- NO updated_at
```
Snapshot of transaction_lines — immutable. Used for DO print and stock deduction reference.
Index: `(do_id)`.

---

### `returns`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
return_no       VARCHAR(30) NOT NULL UNIQUE    -- e.g. RET-A01-20260225-0001
do_id           BIGINT UNSIGNED NULL FK→delivery_orders.id    -- NULL for Ambil return (rare)
transaction_id  BIGINT UNSIGNED NOT NULL FK→transactions.id
branch_id       BIGINT UNSIGNED NOT NULL FK→branches.id
return_type     ENUM('partial','full') NOT NULL
reason          TEXT NOT NULL
status          ENUM('pending','processed') NOT NULL DEFAULT 'pending'
processed_by    BIGINT UNSIGNED NULL FK→users.id
processed_at    TIMESTAMP NULL
created_by      BIGINT UNSIGNED NOT NULL FK→users.id
created_at, updated_at
```
Index: `(transaction_id)`, `(do_id)`, `(branch_id, status)`.

---

### `return_lines`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
return_id       BIGINT UNSIGNED NOT NULL FK→returns.id
product_id      BIGINT UNSIGNED NOT NULL FK→products.id
qty_returned    DECIMAL(15,3) NOT NULL
hpp_snapshot    DECIMAL(15,2) NOT NULL    -- HPP from original transaction_line
created_at      TIMESTAMP NOT NULL
```
Index: `(return_id)`.

---

### `receivables`
```
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
transaction_id      BIGINT UNSIGNED NOT NULL UNIQUE FK→transactions.id
customer_id         BIGINT UNSIGNED NULL FK→customers.id
branch_id           BIGINT UNSIGNED NOT NULL FK→branches.id
total_amount        DECIMAL(15,2) NOT NULL
paid_amount         DECIMAL(15,2) NOT NULL DEFAULT 0
outstanding_amount  DECIMAL(15,2) NOT NULL    -- kept in sync: total - paid
due_date            DATE NOT NULL
status              ENUM('unpaid','partially_paid','paid','cancelled') NOT NULL DEFAULT 'unpaid'
created_at, updated_at
```
Index: `(transaction_id)` (UNIQUE), `(customer_id, due_date)`, `(status, due_date)` for aging queries, `(branch_id, status)`.

---

### `receivable_payments`
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
receivable_id   BIGINT UNSIGNED NOT NULL FK→receivables.id
amount_paid     DECIMAL(15,2) NOT NULL
payment_method  ENUM('cash','transfer') NOT NULL
payment_date    DATE NOT NULL
notes           VARCHAR(255) NULL
created_by      BIGINT UNSIGNED NOT NULL FK→users.id
created_at      TIMESTAMP NOT NULL    -- NO updated_at — immutable payment record
```
Index: `(receivable_id, created_at)`.

---

## Indexes and Uniqueness Summary

| Table | Index / Unique Constraint |
|---|---|
| `users` | UNIQUE(email) |
| `roles` | UNIQUE(name) |
| `permissions` | UNIQUE(slug) |
| `role_permissions` | PRIMARY KEY(role_id, permission_id) |
| `user_branch_access` | PRIMARY KEY(user_id, branch_id) |
| `audit_logs` | INDEX(auditable_type, auditable_id), INDEX(user_id, created_at) |
| `branches` | UNIQUE(code) |
| `products` | UNIQUE(sku), INDEX(barcode), INDEX(category_id) |
| `product_prices` | UNIQUE(product_id) |
| `branch_product_prices` | UNIQUE(branch_id, product_id) |
| `stock_balances` | UNIQUE(product_id, branch_id) |
| `stock_ledger` | INDEX(product_id, branch_id, created_at), INDEX(source_type, source_id) |
| `transactions` | UNIQUE(transaction_no), INDEX(branch_id, transaction_date), INDEX(payment_status), INDEX(customer_id) |
| `delivery_orders` | UNIQUE(do_no), UNIQUE(transaction_id), INDEX(branch_id, fulfillment_status) |
| `receivables` | UNIQUE(transaction_id), INDEX(customer_id, due_date), INDEX(status, due_date) |

---

## Branch-Level Segregation Approach

All operational tables include `branch_id`. Access control is enforced at two levels:

1. **Query scope** — All Eloquent queries for non-Super Admin / non-Owner users are automatically scoped to the user's assigned branches via a global scope or middleware.
2. **Policy layer** — Every write action verifies `request.user.branch_id == resource.branch_id`.

`stock_balances` and `stock_ledger` are keyed by `branch_id` — inter-branch stock is never mixed.

---

## Immutable Ledgers

### stock_ledger
- Written by: `InventoryEngine` service class only
- Never updated or deleted
- If a mistake occurs: create a compensating entry (e.g. adjustment_in/out) with reason
- Recomputing `stock_balances.qty_on_hand` = `SELECT SUM(qty_change) FROM stock_ledger WHERE product_id=X AND branch_id=Y`

### audit_logs
- Written by: `AuditLogger` service class (called from Model observers + manual calls)
- Never updated or deleted
- No `updated_at` column (intentional signal)
- Old/new values stored as JSON (MySQL JSON type — indexed for auditable_type+id lookups)

---

## Snapshot Storage Rules

### transaction_lines (snapshot at creation)
```
unit_price_snapshot  = product's effective price at transaction date (branch override or global)
hpp_snapshot         = products.hpp_current at transaction date
margin_snapshot      = unit_price_snapshot - hpp_snapshot
discount_line        = applied discount per unit × qty
subtotal             = (unit_price_snapshot - discount_line) × qty
```
These values are stored at `POST /transactions` time. They do not change afterward.

### return_lines (snapshot from original transaction)
```
hpp_snapshot = copied from original transaction_lines.hpp_snapshot
```
Used for accounting reversal entries in Phase 3.

### delivery_order_lines (snapshot from transaction_lines)
```
qty = copied from transaction_lines.qty at DO creation
```
Used for DO print and InDelivery stock deduction.

---

## Phase 3 Accounting Readiness (Safe Mode)

Enforced from Phase 1 so Phase 3 needs no schema changes:

| Data Point | Stored In | Phase 1? |
|---|---|---|
| Transaction type (sale/return/purchase) | transactions.transaction_mode + model type | Yes |
| Gross amount | transactions.gross_amount | Yes |
| Discount | transactions.discount_amount | Yes |
| HPP per line | transaction_lines.hpp_snapshot | Yes |
| Selling price per line | transaction_lines.unit_price_snapshot | Yes |
| Payment method | transactions.payment_method | Yes |
| Branch | transactions.branch_id | Yes |
| Transaction date | transactions.transaction_date | Yes |
| Customer (for AR) | transactions.customer_id + receivables | Yes |
| Basic COA codes | chart_of_accounts (seeded) | Yes |

When Phase 3 accounting module activates, it reads these Phase 1 records and generates `journal_entries` + `journal_lines` without any data re-entry.

---

## MySQL and Shared Hosting Constraints

| Item | Decision |
|---|---|
| Character set | `utf8mb4` — supports full Unicode including emoji in notes |
| Collation | `utf8mb4_unicode_ci` — case-insensitive search |
| Storage engine | InnoDB (all tables) — FK support, row-level locking |
| Monetary columns | DECIMAL(15,2) — max 999,999,999,999,999.99 — sufficient for multi-cabang |
| Qty columns | DECIMAL(15,3) — supports fractional quantities (e.g. 0.5 karung) |
| JSON columns | Used only in audit_logs (MySQL 8.0 native JSON) |
| Migrations | Run on deploy; never `--force` on production without backup |
| Connection pooling | Laravel default (persistent: false); keep transactions short |
| Queue table | Laravel default `jobs` table; dispatcher runs via cron |
| Session table | Database sessions (`sessions` table) — no Redis dependency |
| Soft deletes | `deleted_at` on: users, branches, warehouses, categories, units, products, customers, suppliers |
| No FULLTEXT | Use LIKE queries for product search (sufficient for small datasets; optimize with index if needed) |
