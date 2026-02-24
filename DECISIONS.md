# DECISIONS.md — Decision Log
Purpose: prevent design drift across days and across AI sessions.

Format:
- Date (YYYY-MM-DD)
- Decision ID (DEC-0001)
- Context
- Decision
- Rationale
- Consequences/Tradeoffs
- References (AGENTS.md section + SPEC.md section)

---

## Example
### 2026-02-25 — DEC-0001: Use dual status layer
Context: Need to avoid ambiguous “Completed”.
Decision: Use PaymentStatus + FulfillmentStatus two-layer status.
Rationale: Prevent ambiguity; aligns with architectural stability requirements.
Consequences: Slightly more fields; clearer reporting and logic.
References: AGENTS.md §4, SPEC.md §3