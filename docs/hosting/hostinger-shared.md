# Hostinger Shared Hosting Notes (Operational Constraints)

## Known constraints (plan-dependent)
- Cron jobs: limited on some plans (e.g., Single has a maximum). :contentReference[oaicite:4]{index=4}
- PHP parameters and resource limits vary by plan; check hPanel PHP Configuration. :contentReference[oaicite:5]{index=5}
- MySQL limits exist (connections / per-hour). :contentReference[oaicite:6]{index=6}

## Strategy
- Prefer scheduler via cron: `php artisan schedule:run`
- No Supervisor: avoid always-on workers; use short-lived queue workers
- Keep jobs small and idempotent
- Use database queue driver (no Redis requirement)
- Keep storage/logging bounded; rotate logs

## Deployment notes
- Use .env for config; never commit secrets
- Use `php artisan config:cache` and `route:cache` cautiously (shared hosting debugging)