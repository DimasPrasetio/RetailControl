# docs/00_project_canonical_brief.md
# Project Canonical Brief — Retail Control System Modular
Last synced: 2026-02-25
Authority chain: AGENTS.md > SPEC.md > DECISIONS.md > PDFs

---

## 1. Core Objectives (5 — immutable)

1. Real-time stock control per branch (cabang)
2. Support POS transactions: Ambil Langsung & Kirim (Delivery)
3. Manage AR (piutang pelanggan) and AP (utang supplier)
4. Generate basic financial reports (integrated basic accounting)
5. Provide owner visibility — operational & financial dashboard

Any feature that does not directly serve one of these five objectives is deferred or rejected.

---

## 2. Hard Exclusions (Anti-ERP Boundaries)

The following are **explicitly OUT OF SCOPE** forever unless a formal scope-change decision is recorded in DECISIONS.md:

| Category | Excluded Feature |
|---|---|
| Financial | Multi-currency, budgeting system, cost center matrix, deferred revenue engine |
| Financial | Retroactive HPP recalculation, complex closing & auto reversal of closed periods |
| Financial | Multi-entity consolidation, ERP-level budgeting, complex accrual automation |
| Technical | Event sourcing, message brokers, microservices |
| Technical | Full WMS (Warehouse Management System) |
| Integration | Bank/tax real-time API integrations, third-party API integrations |
| Operations | GPS tracking for couriers, offline full synchronization |
| Workflow | ERP-level multi-layer approval workflows |

**Principle:** If a proposed feature resembles any of the above, reject without review.

---

## 3. Canonical Status Definitions

### 3.1 PaymentStatus
Applied to: `transactions.payment_status`

| Value | Meaning |
|---|---|
| `Paid` | Cash or transfer — fully settled at creation |
| `Credit` | AR created; no payment yet; due_date required |
| `PartiallyPaid` | Some payments received; outstanding > 0 |
| `Cancelled` | Transaction voided; audit log required |

Transitions: `Credit → PartiallyPaid → Paid` | `Any → Cancelled` (only if pre-lock)

### 3.2 FulfillmentStatus
Applied to: `delivery_orders.fulfillment_status` (Kirim transactions only)

| Value | Meaning |
|---|---|
| `Draft` | DO auto-created; awaiting admin release |
| `Released` | Admin released; handed to warehouse |
| `InDelivery` | Goods left warehouse; stock deducted |
| `Delivered` | Customer received & signed |
| `Failed` | Delivery failed; awaiting return |
| `Returned` | Goods returned; stock restored |

Transitions: `Draft → Released → InDelivery → Delivered`
Failure path: `InDelivery → Failed → Returned`

**CRITICAL:** The term `Completed` is **PROHIBITED**. It is ambiguous (does it mean paid? delivered? both?). This is a non-negotiable guardrail from AGENTS.md §4.

### 3.3 Transaction Mode
Applied to: `transactions.transaction_mode`

| Value | Meaning |
|---|---|
| `AmbilLangsung` | Customer takes goods immediately; no DO created |
| `Kirim` | Goods to be delivered; DO created in Draft |

Ambil Langsung transactions have **no FulfillmentStatus** — that column does not apply.

---

## 4. Inventory Deduction Timing Rules

| Trigger | Event | Stock Action | Ledger Written? |
|---|---|---|---|
| Ambil Langsung (Paid or Credit) | Transaction SAVED | Deduct immediately | YES |
| Kirim (any payment) | Transaction saved | NO deduction yet | No |
| Kirim — DO Draft | DO created | NO deduction | No |
| Kirim — DO Released | Admin releases | NO deduction | No |
| Kirim — DO → InDelivery | Admin marks InDelivery | **Deduct now** | YES |
| Return (any type) | Return document created & processed | Add back | YES |
| Stock Adjustment | Adjustment saved with reason | +/- | YES |
| Purchase Received | Goods receipt confirmed | Add | YES |

**Invariant I1 (SPEC.md):** No stock change without a `stock_ledger` entry. Violation = critical bug.

---

## 5. HPP Snapshot Locking Rules

### 5.1 What is stored at transaction creation
Every `transaction_lines` row must store:

```
unit_price_snapshot    — selling price at time of transaction
hpp_snapshot           — moving-average HPP at time of transaction
margin_snapshot        — unit_price_snapshot - hpp_snapshot
discount_line          — per-line discount amount
subtotal               — (unit_price_snapshot - discount_line) × qty
```

The parent `transactions` record must also store:
```
branch_id, transaction_date, gross_amount, discount_amount, net_amount
payment_method, payment_status, transaction_mode
```

This data set must be sufficient for Phase 3 accounting journal generation without any additional lookups.

### 5.2 What NEVER changes after creation
- `hpp_snapshot` on any transaction_line — immutable forever
- `unit_price_snapshot` on any transaction_line — immutable forever
- Historical period reports — derived from snapshots, never recalculated

### 5.3 Moving Average continues forward
- When new stock is received (Purchase), `products.hpp_current` is recalculated via moving average
- This new HPP applies only to **future** transactions
- Past transactions retain their `hpp_snapshot` unchanged

---

## 6. Locking and Mutability Rules

### 6.1 Before any DO (Ambil Langsung)
- Transaction is immutable once saved
- Correction only via formal Void (with reason + audit) if not yet physically fulfilled

### 6.2 After DO Draft created (Kirim)
- `transaction_lines`: qty and product list are **IMMUTABLE**
- `transactions` header: editable (delivery address, notes) — but NOT financial fields
- To correct lines: Void DO → edit transaction → generate new DO
- All of this requires audit log entries

### 6.3 After DO Released
- Entire transaction is **FULLY IMMUTABLE**
- No direct edits allowed
- Changes only via:
  - **Return partial** — creates return document, restores stock
  - **Return full** — creates return document, restores all stock
  - **Adjustment** — formal correction with mandatory reason + audit log

### 6.4 Silent Modification Prohibition
`SPEC.md I3`: No modification without an audit trail entry. This applies to:
- Transaction records
- DO status changes
- Stock balance changes
- Price changes
- User/role changes

---

## 7. Audit Trail Rules

### 7.1 audit_logs table (immutable — no UPDATE/DELETE ever)
Every sensitive action must create a row:

```
user_id           — who performed the action
action            — create | update | delete | status_change | void
auditable_type    — model class name (e.g. Transaction, DeliveryOrder)
auditable_id      — primary key of the affected record
old_values        — JSON snapshot of values before change (null for creates)
new_values        — JSON snapshot of values after change (null for deletes)
ip_address        — request IP
user_agent        — browser/device
created_at        — timestamp (UTC)
```

`audit_logs` rows are **append-only**. Application code must never issue `UPDATE` or `DELETE` on this table.

### 7.2 stock_ledger (immutable ledger)
Similarly append-only. No row is ever updated or deleted. Current balance is always derivable from `SUM(qty_change)` per `product_id + branch_id`.

### 7.3 What triggers an audit log
- User login / logout
- Any CRUD on: users, roles, products, branches, warehouses, customers, suppliers, prices
- Any financial transaction: create, void, cancel
- DO status transitions
- AR/AP payment recording
- Stock adjustments
- Any permission or access change

---

## 8. Hostinger Shared Hosting Constraints and Design Impact

| Constraint | Impact on Design |
|---|---|
| No Supervisor / no persistent daemon | Queue driver = `database`. Workers run via cron: `php artisan schedule:run` every minute. Jobs use `--stop-when-empty` pattern. |
| Cron job limit (plan-dependent) | Use single cron entry → Laravel Scheduler dispatches all sub-jobs |
| PHP execution time limit | No long-running requests. Chunk large operations (bulk updates) into jobs. POS transaction must complete in < 5s. |
| MySQL connection limits | Use eager loading. Keep DB transactions short. Avoid N+1. |
| No Redis | Queue driver: `database`. Cache driver: `file` (or `database`). Sessions: `database`. |
| Storage limits | Log rotation enforced. Use `daily` log channel. No unbounded file growth. |
| No WebSocket server | "Real-time" updates via page refresh or polling. No Pusher dependency. |
| Shared environment | Never use `config:cache` in development. Use `.env` for all secrets. |
| Deployment | `php artisan optimize` cautiously. `route:cache` only when routes are stable. |

**Design implications:**
- All background processes must be idempotent (safe to re-run if cron fires twice)
- Receipt/report generation is synchronous (fast enough) or queued with status polling
- Bulk price update is queued as a job, not processed in-request

---

## 9. Ambiguity and Contradiction Resolution

The following contradictions were found across documents. Canonical resolution is documented here.

### C1 — "Completed" status in PDFs vs AGENTS.md prohibition
- **PDF 1 §D1** and **PDF 3 §Skenario A** use "Status: Completed" for Ambil Langsung lunas.
- **AGENTS.md §4** explicitly prohibits "Completed" as ambiguous.
- **Resolution (AGENTS.md wins):** Ambil Langsung lunas = `PaymentStatus: Paid`. There is no `FulfillmentStatus` for Ambil Langsung. The term "Completed" must never appear in any enum, column, or UI label.

### C2 — FulfillmentStatus applicability to Ambil Langsung
- **SPEC.md §3.2** mentions "N/A or a 'NoFulfillment' internal flag" for Ambil.
- **PDF 4 §4.2** shows FulfillmentStatus enum only for DO.
- **Resolution:** `FulfillmentStatus` exists only on `delivery_orders` table. `transactions` table does not have a `fulfillment_status` column. Ambil Langsung transactions have no `delivery_orders` row.

### C3 — "Returned" in FulfillmentStatus
- **PDF 1 §E** lists DO lifecycle as only 5 states: Draft, Released, InDelivery, Delivered, Failed. No "Returned".
- **AGENTS.md §4.2** and **PDF 4 §4.2** include "Returned" as a valid FulfillmentStatus.
- **Resolution (AGENTS.md wins):** `Returned` is a valid `FulfillmentStatus`. It is the terminal state after `Failed` + return processed.

### C4 — Accounting phase
- **PDF 1 §4** places Basic Accounting in Phase 3.
- **PDF 4 §6** requires Phase 1 to store sufficient data (hpp_snapshot, gross_amount, etc.) for future journal generation.
- **Resolution (no conflict):** Accounting *module* (journal generation, reports) is Phase 3. But the *data foundation* (snapshots, transaction types, payment methods) is enforced from Phase 1. Both are true simultaneously. DB blueprint reflects this.

### C5 — Transfer antar cabang phase
- **PDF 1 §4** places inter-branch transfer in Phase 2.
- **DB design should be ready** (stock_ledger movement_type includes `transfer_in` | `transfer_out`) but the UI/feature is Phase 2.
- **Resolution:** Schema is future-proofed; implementation is deferred.

---

*This document is authoritative. Code that contradicts it must change, not this document — unless a new entry in DECISIONS.md explicitly overrides a specific rule.*
