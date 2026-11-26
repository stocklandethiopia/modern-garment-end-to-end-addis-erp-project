# Angular Current Capabilities and Adoption Standard

**Snapshot date:** 26 November 2026  
**Project baseline:** Angular 22.x / Angular CLI 22.x / TypeScript 6.0.x / Node.js 26.13.1 LTS  
**Purpose:** Apply modern Angular features where they materially improve the enterprise ERP without destabilizing the approved stack or adopting unreleased framework source.

## 1. Source and version discipline

The official [Angular repository](https://github.com/angular/angular/tree/main) is the first-party source for framework implementation, documentation, roadmap and changes. At the time of this review, the observed main head was commit [63fa515ec74cb3690c2c48587d627c0c3120d0f0](https://github.com/angular/angular/commit/63fa515ec74cb3690c2c48587d627c0c3120d0f0), dated 25 November 2026.

The main branch is a changing development feed. Its Angular core package manifest reports version 0.0.0-PLACEHOLDER; it is not the stable release to install into the ERP. The team must use the version actually resolved in the committed package-lock.json, test supported updates through a branch/CI and avoid floating main, canary or placeholder packages in production.

Primary references:
- [Angular official roadmap](https://github.com/angular/angular/blob/main/adev/src/content/reference/roadmap.md)
- [Angular official best practices](https://github.com/angular/angular/blob/main/packages/core/resources/best-practices.md)
- [Angular Signals](https://angular.dev/guide/signals)
- [Signal Forms](https://angular.dev/guide/forms/signals/overview)
- [Linked signals](https://angular.dev/guide/signals/linked-signal)
- [Resource API](https://angular.dev/guide/signals/resource) and [httpResource](https://angular.dev/guide/http/http-resource)
- [Zoneless change detection](https://angular.dev/guide/zoneless)
- [Hydration](https://angular.dev/guide/hydration) and [incremental hydration](https://angular.dev/guide/incremental-hydration)
- [Deferrable views](https://angular.dev/guide/templates/defer)

## 2. Roadmap maturity observed on 26 November 2026

The live roadmap lists the following capabilities under “Production ready”:
- Signal Forms.
- Resource API and httpResource.
- Zoneless change detection.
- Linked Signal API.
- Incremental hydration.
- Effect API.
- Event replay with SSR.
- Route-level render mode.

The same roadmap lists Web MCP separately as “Available to experiment with”. Treat it as a controlled pilot only. Do not make an experimental tool-protocol feature a production prerequisite or a route around Laravel authorization.

Angular v22 promotes Angular Aria to stable; it provides accessible headless interaction patterns. Use Angular Aria, CDK or Material as appropriate to the component and design-system needs rather than inventing inaccessible controls.

## 3. Project adoption rules

### Component and application structure

- Use standalone components (standalone is the default in modern Angular); do not add redundant standalone: true.
- Keep the domain/feature structure already used by this ERP. Lazy-load larger feature routes and keep each component focused.
- Use strict TypeScript and strict template checking. Prefer explicit domain types and unknown for untrusted data; avoid any.
- Use input(), output() and model() APIs for new component contracts where they improve typed flow. Keep old, working components stable until a bounded feature refactor is justified.
- Angular v22 uses OnPush-style change detection by default. Do not repeat an explicit OnPush setting unless a compatibility reason is documented.
- Import only the standalone directives/pipes used by a template. Prefer class and style bindings over ngClass/ngStyle as recommended by Angular.
- Use NgOptimizedImage for applicable static images; test dimensions, loading priority and accessibility.

### Signals and state

- Use signal() for local reactive state, computed() for derived state and linkedSignal() where a selection must remain valid as upstream options change.
- Keep transactional and durable state on the server. Angular signals are UI state, not an inventory, approval or finance source of truth.
- Keep state transitions explicit. Do not use effect() as a substitute for deriving state or as an invisible place to execute business transactions.
- Use Resource/httpResource for reactive asynchronous reads whose loading/error/value lifecycle should be represented as signals.
- Keep mutations (approve, reserve, receive, dispatch, post, pay, release) behind typed Angular API services calling Laravel commands with correlation/idempotency and server authorization. Do not use a reactive read resource to silently trigger a material transaction.

### Forms and validation

- Use stable Signal Forms (@angular/forms/signals) for new form experiences where schema-based validation, typed field access and signal-native state are helpful.
- Existing Angular Reactive Forms remain a valid choice; do not mass-migrate the current ERP just to use a newer API. Select a bounded feature, prove accessibility, tests and maintainability, then reuse the pattern.
- Client validation improves usability but never replaces Laravel validation, domain invariants, database constraints or permission checks.
- For material changes, show the exact affected record/revision, validation messages, warnings and confirmation/approval state.

### Performance, hydration and large workspaces

- Use route-level lazy loading and @defer for non-critical heavy views (for example large analytics panels, advanced charts, document previews and specialist planning views). Include accessible placeholder/loading/error states, avoid avoidable layout shifts and test focus/screen-reader behavior.
- Treat SSR/hydration as a measured architectural choice, not an automatic requirement for every authenticated ERP screen. Personalized tenant, payroll, customer and finance content must never be shared through an incorrectly cacheable public render.
- Use incremental hydration only where SSR/hydration has already been introduced and a measured initial-load benefit justifies the added complexity. Preserve identical server/client DOM expectations and test the exact route.
- For zoneless compatibility, use Angular notification mechanisms and signal-driven rendering. In any existing component still depending on Reactive Forms or external observables, add explicit signal/notification bridging and tests before removing ZoneJS from that feature.

### Accessibility and testing

- Use semantic HTML, proper labels, keyboard navigation, visible focus, logical focus management, accessible announcements for deferred content and WCAG 2.2 AA contrast/interaction expectations.
- Run axe or an equivalent automated audit and supplement it with keyboard and screen-reader checks on critical workflows.
- Vitest is Angular's primary test runner in the current roadmap. Use the Angular CLI builder and the project's pinned tooling; do not replace the runner or builder without an evidence-backed change.
- Test loading, empty, error, forbidden, retry, stale data, concurrent updates, idempotent replays and long-running workflow states—not only the happy path.

## 4. ERP feature application map

| ERP feature | Angular pattern to prefer |
|---|---|
| Live production line/shift board | Signal-driven state, typed event stream, computed totals, controlled polling/subscription lifecycle |
| Monthly capacity and line-balancing workbench | Lazy route, typed Resource reads, computed/linked selections, schema-validated planning form |
| Inline quality workbench | Fast scan/search UI, form state and local validation; server-sourced plan/spec; prominent hold and approval state |
| Product/tech-pack revision | Version-pinned forms, linked variant/material selections, file upload progress, immutable server-approved snapshot |
| Warehouse/QR/barcode station | Keyboard/wedge and camera adapter behind a scan interface; duplicate/error state; command goes through Laravel |
| Analytics/control tower | Lazy/deferrable visualizations with explicit data freshness and source-event drill-down |
| Persistent operational assistant | Context-aware panel with typed action cards; server-provided capability registry; no client-generated permission |

## 5. Experimental capability gate

Web MCP and any future dynamic UI/A2UI integration must remain feature-flagged and isolated to synthetic or non-sensitive test data until a separate WP-15 evaluation demonstrates:
- a precise business use case not already served by ordinary API/UI;
- user/tenant authorization and tool allowlist;
- prompt-injection and untrusted-content resistance;
- confirmation for material actions;
- observability, latency/cost bounds, disable switch and provider-outage fallback;
- security, accessibility and end-to-end tests.

An experimental framework feature does not change the approved architecture: Angular presents the ERP, Laravel authorizes and executes, and PostgreSQL remains the source of truth.

## 6. Upgrade and adoption lifecycle

For each new Angular feature or major/minor upgrade: inspect official documentation and changelog; record production-ready/preview/experimental maturity; confirm compatibility with the currently pinned Angular/TypeScript/Node/toolchain; prototype in a feature branch; test accessibility, security, bundle size, performance and critical ERP journeys; update lockfiles; review the exact diff; merge only after required CI succeeds. Keep an explicit revert or feature-disable path.

This document is a technology adoption standard, not a declaration that all listed features have already been implemented in the ERP.
