# SPEC.md — Domain Contract (Single Source of Truth)
This file defines domain meaning, invariants, events, and state machines.
If code contradicts this, code must change (not the spec) unless DECISIONS.md says otherwise.

## 1) Glossary (Canonical Definitions)
- Branch/Cabang: operational unit with its own stock ledger
- Transaction: sales transaction created at POS (Ambil/Kirim), owns financial snapshots
- Delivery Order (DO): fulfillment document for Kirim mode; controls physical release and InDelivery event
- Return: formal reversal of goods movement & financial impact; never edit history
- Adjustment: formal correction entry with reason and audit trail
- Stock Ledger: immutable record of stock movements
- Audit Log: immutable log of who did what and when

## 2) Invariants (Must NEVER be violated)
I1. No stock change without a Stock Ledger entry
I2. No transaction line mutation after locking rules
I3. No silent modification (all changes require audit logs)
I4. Profit/report for a locked period must not change due to retroactive recalculation
I5. Payment status and fulfillment status are independent

## 3) State Machines

### 3.1 PaymentStatus transitions
- New Transaction:
  - Paid (cash/transfer confirmed)
  - Credit (AR created with due date)
- Credit -> PartiallyPaid -> Paid
- Any -> Cancelled (only if not locked; must preserve audit)

### 3.2 FulfillmentStatus transitions (Delivery)
- Draft -> Released -> InDelivery -> Delivered
- InDelivery -> Failed
- Failed -> Returned (after return processed)
- Delivered -> Returned (if return after delivery; must be formal)

## 4) Domain Events (Canonical)
E1 TransactionCreated
- creates transaction header + lines
- stores hpp_snapshot, price_snapshot (per line)
- assigns PaymentStatus (Paid/Credit) and FulfillmentStatus (Draft if Kirim else N/A or a “NoFulfillment” internal flag)
- Ambil: triggers StockDeducted immediately
- Kirim: creates DO in Draft (no stock deduction yet)

E2 DOReleased
- marks DO Released
- locks transaction from edits (see AGENTS.md)

E3 DOInDelivery
- marks DO InDelivery
- triggers StockDeducted (Kirim only)

E4 DODelivered
- marks DO Delivered

E5 DOFailed
- marks DO Failed

E6 ReturnCreated (partial/full)
- creates return document
- triggers StockIncreased
- triggers accounting reversal/adjust entries as per accounting rules

E7 ARPaymentReceived
- updates PaymentStatus (PartiallyPaid/Paid)
- records payment ledger entry

E8 PurchaseReceived
- increases stock
- creates AP if credit purchase

## 5) Accounting Rules (Basic Integrated)
- At minimum, system stores sufficient transactional data from Phase 1 to generate journals later
- Journals are generated from events; NEVER from “editing history”

(Chart of Accounts mapping is documented in /docs/accounting/coa.md)

## 6) Hosting Constraints (Operational)
- All background tasks must be idempotent and safe to rerun (cron can retry)
- Avoid tasks that require > PHP max execution time
- Avoid high DB connections; use pooling best practices and short transactions
Hostinger PHP/MySQL limits vary by plan; check current values in hPanel docs. :contentReference[oaicite:3]{index=3}