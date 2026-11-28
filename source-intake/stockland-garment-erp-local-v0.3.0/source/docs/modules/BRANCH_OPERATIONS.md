# Branch operations

## Daily closing

Each branch records business date, opening cash, counted cash, expected payment-channel totals and variance. The record starts as `submitted` and is transitioned to `closed` by an authorized role within the location scope.

## Operations summary

The Command Centre can read counts of suppliers, customers, open purchase orders, open requisitions, active reservations, active employees and posted journals. Location-based roles receive scoped counts.

## Standard locations

- `CENTRAL_WAREHOUSE` — Central Warehouse
- `GORO_BRANCH` — Goro Branch
- `GERJI_BRANCH` — Gerji Branch
- `MEXICO_BRANCH` — Mexico Branch
- `MEGENAGNA_BRANCH` — Megenagna Branch

## Tables

`locations`, `daily_closings`, `users`, `audit_log`, `number_sequences`.
