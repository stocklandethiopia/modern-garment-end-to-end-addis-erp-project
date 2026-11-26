# Miret and Angular adoption update — 26 November 2026

## Purpose and governance

This is a record of the owner-directed Miret clarification and current Angular technology review. It refines the existing garment ERP product goal and the fixed WP-00–WP-16 sequence. It does not create a new work package, reorder the plan, close pending implementation work or authorize production deployment.

The work is on the isolated candidate branch:

- Repository: stocklandethiopia/modern-garment-end-to-end-addis-erp-project
- Candidate branch: work/wp01-16-completion-2026-11-26
- Base for draft review: delivery/autonomous-wp-01-16
- Pull request: [#5 — draft, open and unmerged](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/5)

## Miret system

The project now has a shared four-pillar operating model:

1. Product identity and integrity — versioned identity and traceability from initiative/style/variant/SKU through BOM/material lots, process/operation, bundle/carton, shipment, sale and return.
2. Quality at source — effective product/customer/market/process specifications and inspection plans from supplier/material incoming inspection through first-piece, inline, end-line, special process, final, packing, shipment, distribution receipt and customer return.
3. Industrial/business efficiency — monthly/seasonal capacity, MPS, materials/resources, daily per-line and shift plan, hourly control and shift handover. Preserve the approved WP-10 SAM-based target formula; show accepted good, rejects, rework, scrap, WIP and constraint reason separately.
4. Modern technology and optimization — the approved Angular/Laravel/PostgreSQL/Redis/Python/Docker stack with operational agents tied to actual user roles and authorized domain capabilities.

The detailed cross-WP model and five mandatory acceptance journeys are in [Miret — Four-Pillar Garment Operating Model](../product/miret-system-operating-model.md). The updates intentionally preserve the existing package statuses and do not claim factory calibration, customer sign-off or completed runtime features.

## Angular official-source review

Reviewed the official Angular repository main branch, roadmap and best-practices guide on 26 November 2026. The observed main head was 63fa515ec74cb3690c2c48587d627c0c3120d0f0 (25 November 2026). The main branch is a moving development feed and its core package manifest used a placeholder version; it is not a production dependency.

The official roadmap at review time listed Signal Forms, Resource/httpResource, zoneless change detection, Linked Signal, incremental hydration, Effect API, event replay with SSR and route-level render mode as production ready. Web MCP was listed separately as available to experiment with. Project adoption and safety gates are in [Angular Current Capabilities and Adoption Standard](../architecture/angular-current-capabilities-and-adoption-standard.md). Existing app remains on the pinned Angular 22 / CLI 22 / TypeScript 6 / Node 26 baseline unless a separate versioned and tested change is approved.

## International quality sources captured

The quality registry design now points to the current official snapshots at review time: ISO 9001:2026, ISO 2859-1:2026, ISO/IEC 17025:2017 and AATCC 2026 Volume 101. The application must scope every standard/test/sampling scheme to buyer, product, material, process and market; no universal AQL or certification claim is introduced. Qualified customer, legal and technical approvals remain necessary.

## Notion records synchronized

The following existing Notion pages were appended with the same owner-directed Miret and technology requirements:

- Garment Expert Product Goal — Initiative-to-Sell-Through Operating System
- End-to-End ERP Technology & Intelligent Experience Blueprint — Production to Customer
- Modern Garment ERP — Agent-Native Technology Integration Standard
- Stockland ERP Functional Acceptance Runbook

No duplicate Notion page was created.

## Verification and release boundary

The controlled Composer security workflow passed on candidate run [#45880844205](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45880844205). It validated composer.json, ran composer audit with no security advisories, verified the Symfony fixed-version floor and committed backend/composer.lock with Symfony 8.1.12 for HTTP Foundation, HTTP Kernel, Mailer and Routing.

WP-01 R3 package verification has passed on prior candidate commits, including [run #45880744744](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45880744744). That is not a pass for the combined ERP candidate. PR-triggered workflows on the bot-generated lockfile commit returned action_required before starting jobs. The next owner-triggered exact-head run must verify Angular, PostgreSQL/Laravel migrations, RLS, tests, formatting, route registration and combined artifact publication.

A repository commit, design document, successful Composer audit or separate WP-01 run is not the combined ERP release. Do not merge PR #5, publish a release artifact as production-ready, deploy or claim complete Miret runtime coverage until the required exact-head checks and independent business/security/professional gates pass.

## 26 November 2026 — PHP correction and formatting evidence

- Exact-head run #45881080663: WP-01 R3 source checks passed; Angular audit, unit tests and production build passed; PostgreSQL 18 migrations and forced-RLS verification passed; backend stopped at Pint before Laravel tests.
- Corrective finding: Pint identified a malformed DateTimeImmutable namespace in IndustrialEngineeringService and 31 formatting issues. The namespace was corrected in commit 55fd21eaec99f870c5206b04ea15038544bcbc73.
- Candidate formatter workflow #45881390947 passed: Pint formatting applied, every PHP file under app/bootstrap/config/database/routes/tests passed php -l, and the follow-up Pint --test gate passed. Formatter output was committed as style-only commit 8a65cfd9263594474d2a38026cc72b6654603756.
- The Laravel test suite and route registration did not run on the earlier failing SHA. They must run on the next owner-triggered exact candidate head. Artifact assembly remains blocked until all three required jobs pass on one SHA.
