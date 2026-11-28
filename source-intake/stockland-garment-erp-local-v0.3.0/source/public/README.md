# `public/` — Stockland browser application and PWA

**Owner:** Frontend Engineering  
**Application version:** 0.3.0  
**Runtime:** same-origin assets served by `src/server.mjs`.

## File inventory

| File | Responsibility |
|---|---|
| `index.html` | Semantic browser shell, metadata and asset loading |
| `app.js` | Session bootstrap, role-aware navigation, dashboard, module views, forms, CSV parsing and API integration |
| `app.css` | Responsive visual system, navigation, form controls, tables, cards and mobile layouts |
| `manifest.webmanifest` | PWA identity, app name, start URL, display and theme configuration |
| `sw.js` | Versioned application-shell cache and offline static asset support |
| `physical-count-template.csv` | Browser-accessible physical count worksheet |
| `icons/stockland.svg` | Stockland ERP application icon |

## User interface modules

Overview, Product Master, Suppliers, Customers & Resellers, Procurement & Receiving, Inventory & Transfers, Branch Requisitions, Reservations, Physical Stock Count, Retail POS, Returns & Damage, Wholesale Orders, POS Imports & Release, Finance & Accounting, HR & Payroll, Daily Closing, Reports, Users & Access and Audit Trail.

## Frontend conventions

- Escape user-controlled text before rendering HTML.
- Use the same-origin API for persisted state changes.
- Include CSRF tokens for authenticated state-changing requests.
- Keep role and location decisions authoritative in API handlers.
- Preserve responsive layout, keyboard focus and table overflow behavior.
- Keep static assets versioned with the PWA cache identifier.

## Related documents

- [Architecture](../docs/ARCHITECTURE.md)
- [API reference](../docs/API_REFERENCE.md)
- [Security model](../docs/SECURITY_MODEL.md)
- [Test strategy](../docs/TEST_STRATEGY.md)
