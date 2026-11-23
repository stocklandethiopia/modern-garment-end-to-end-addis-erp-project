# Stockland ERP engineering documentation

**Application version:** 0.3.0  
**Schema version:** 2  
**Relational tables:** 45

This directory contains the application's engineering standards and operational reference. Functional requirements, API contracts, schema, security controls, business workflows, tests and release evidence are linked to the current source tree.

## Core documentation

| Document | Purpose |
|---|---|
| [Project charter](PROJECT_CHARTER.md) | Business purpose, users, scope and operating model |
| [Architecture](ARCHITECTURE.md) | Runtime topology, code layers and data ownership |
| [API reference](API_REFERENCE.md) | Implemented REST endpoints, roles and payload conventions |
| [Database schema](DATABASE_SCHEMA.md) | All 45 tables, columns, constraints, relationships and indexes |
| [Security model](SECURITY_MODEL.md) | Authentication, authorization, location scope and data integrity |
| [Workflows](WORKFLOWS.md) | End-to-end business processes |
| [POS import release](POS_IMPORT_RELEASE.md) | Final Branch Manager release and atomic posting rules |
| [Technical requirements](TECHNICAL_REQUIREMENTS.md) | Runtime, environment and data standards |
| [Test strategy](TEST_STRATEGY.md) | Acceptance scenarios and reproducible commands |
| [Release gates](RELEASE_GATES.md) | Local and GitHub Actions verification sequence |
| [Verification report](VERIFICATION_REPORT.md) | Executed test and packaging evidence |
| [Module contracts](IMPLEMENTATION_BOUNDARIES.md) | Service boundaries and extension rules |
| [Requirements traceability](REQUIREMENTS_TRACEABILITY.csv) | Requirement, source and test mapping |
| [Open-source provenance](OPEN_SOURCE_PROVENANCE.md) | Upstream source and license records |
| [Operations runbook](OPERATIONS_RUNBOOK.md) | Setup, daily operation, backup and restore |
| [Integrity controls](RISK_REGISTER.md) | Security and business safeguards |
| [Architecture decisions](DECISIONS.md) | Technology and operational decisions |
| [Release notes](RELEASE_NOTES.md) | Version 0.3.0 scope |
| [Extension catalogue](KNOWN_LIMITATIONS.md) | Supported extension interfaces and future integration contracts |
| [Artifact inventory](ARTIFACT_INVENTORY.md) | Project file catalogue |

## Module specifications

- [Product catalogue](modules/PRODUCT_CATALOG.md)
- [Inventory and warehouse](modules/INVENTORY.md)
- [Procurement](modules/PROCUREMENT.md)
- [Retail POS](modules/POS.md)
- [Wholesale and customers](modules/WHOLESALE_CUSTOMERS.md)
- [Finance](modules/FINANCE.md)
- [HR and payroll](modules/HR_PAYROLL.md)
- [Branch operations](modules/BRANCH_OPERATIONS.md)

## Documentation standards

Use `standards/ENGINEERING_DOC_TEMPLATE.md` for module engineering documents and `standards/RELEASE_EVIDENCE_TEMPLATE.md` for validation results. Each implementation folder has a README describing its files and boundaries.
