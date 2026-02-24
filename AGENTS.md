# AGENTS.md — Project Constitution (Non-Negotiables)
Project: Digitalisasi Toko Material Multi Cabang (Retail Control System Modular)
Stack: Laravel 10 + Tailwind + MySQL
Hosting target: Hostinger Shared Hosting (no root, limited background processes)

## 0) Golden Rule
This system is a **Retail Control System Modular**, NOT an ERP.
If a feature increases complexity without directly supporting core objectives, reject or defer.

## 1) Core Objectives (Only these are “must-win”)
1) Real-time stock control per branch
2) Support POS transactions: Ambil Langsung & Kirim (Delivery)
3) Manage AR (piutang) and AP (utang supplier)
4) Generate basic financial reports (integrated basic accounting)
5) Provide owner visibility (operational & financial dashboard)

## 2) Hard Scope Boundary (Explicitly OUT)
- Multi-currency, budgeting, cost center matrix, multi-entity consolidation
- ERP-level approval workflows
- Full WMS, GPS tracking
- Bank/tax real-time integrations
- Retroactive HPP recalculation, event sourcing, microservices early
- Complex closing & auto reversal of closed periods

## 3) Architectural Constraints (Hostinger Shared Hosting)
- Monolith modular (single Laravel app)
- Single MySQL database as Single Source of Truth
- Avoid long-running daemons/workers that require Supervisor (shared hosting typically doesn’t allow it)
- Use cron-driven jobs/scheduler for background processing (bounded & idempotent)
- Keep memory & execution time constraints in mind; optimize for predictable short tasks

Reference (Hostinger limits & PHP params): see Hostinger “parameters and limits” pages. :contentReference[oaicite:0]{index=0}

## 4) Status Standard (NO ambiguous status)
We use TWO independent status layers:

### 4.1 PaymentStatus
- Paid
- Credit
- PartiallyPaid
- Cancelled

### 4.2 FulfillmentStatus (Delivery/fulfillment)
- Draft
- Released
- InDelivery
- Delivered
- Failed
- Returned

Do NOT use single “Completed” status because it is ambiguous.

## 5) Financial Integrity (HPP Snapshot Locking)
At **Transaction creation**, store:
- hpp_snapshot per line item
- selling_price_snapshot per line item
- margin_snapshot per line item (optional but recommended)
- branch_id, transaction_date

Rules:
- Profit for a period must not change after locked events
- NO retroactive HPP recalculation
- Moving Average continues for future transactions, but historical reports stay stable

## 6) Inventory Deduction Rules (Must be consistent)
- Ambil Langsung: stock decreases when transaction is saved
- Kirim (Delivery): stock decreases when DO becomes InDelivery
- Return increases stock (formal return process with audit trail)
All stock movements must create ledger entries + audit record.

## 7) Locking / Mutability Rules (No silent changes)
- After DO Draft created: transaction lines (qty/product) cannot be mutated; only Void DO and regenerate DO (formal)
- After DO Released: transaction is immutable; changes only via:
  - Return partial/full
  - Adjustment (with reason + audit)
No direct edits without audit log.

## 8) Change Protocol (Required for every PR)
Every change must include:
- SPEC.md updated if domain meaning changes
- DECISIONS.md updated if a new rule/decision introduced
- Tests updated/added for impacted scenarios
- Confirm no guardrail violations

## 9) Default Implementation Choices (Shared Hosting Friendly)
- Queue driver: database (or sync initially) — avoid Redis requirement
- Scheduler: cron every minute (or plan limit) to run `php artisan schedule:run`
- For queue: prefer `schedule` to run short `queue:work --stop-when-empty` style tasks, not a perpetual worker

Hostinger cron limit depends on plan (e.g., Single has limited cron jobs). :contentReference[oaicite:1]{index=1}