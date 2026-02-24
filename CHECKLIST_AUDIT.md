# CHECKLIST_AUDIT.md — PR Quality Gate

## Guardrails
- [ ] No ERP features introduced (AGENTS.md §2)
- [ ] No ambiguous status (“Completed”) introduced (AGENTS.md §4)
- [ ] No retroactive HPP recalculation (AGENTS.md §5)

## Core invariants
- [ ] Every stock change writes Stock Ledger entry (SPEC.md I1)
- [ ] Locking rules enforced after DO states (AGENTS.md §7)
- [ ] All edits logged (audit trail)

## Golden scenarios (must pass)
- [ ] Ambil Langsung lunas: stock deducted on save; snapshots stored
- [ ] Ambil Langsung kredit: AR created; stock deducted; due date required
- [ ] Kirim lunas/kredit: DO Draft created; stock deducted only on InDelivery
- [ ] Failed delivery: DO Failed + Return restores stock