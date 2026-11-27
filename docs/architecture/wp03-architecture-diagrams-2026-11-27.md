# WP-03 Architecture Diagrams — Source Set

**Document ID:** WP03-DIAGRAMS-001  
**Version:** 1.0 · **Date:** 27 November 2026  
**Status:** Target design diagrams. Diagrams do not prove implementation.

## 1. Context and trust boundaries

~~~mermaid
flowchart LR
  subgraph U["Untrusted channels"]
    B[Browser / Angular]
    DEV[Scanner / device]
    EXT[External integrations]
    AI[AI / agent content]
  end
  subgraph API["Authenticated application boundary"]
    AUTH[Authentication and session]
    TEN[Tenant / actor context resolver]
    ENT[Enterprise services]
    IAM[Identity and access]
    DOA[Position and DoA resolver]
    DOM[Domain application services]
  end
  subgraph DB["Authoritative data boundary"]
    PG[(PostgreSQL 18 + forced RLS)]
    AUD[Append-only audit]
    OUT[Transactional outbox]
  end
  subgraph ASYNC["Asynchronous / non-authoritative services"]
    REDIS[(Redis queue/cache)]
    WORKER[Laravel workers]
    ADAPTER[External adapters]
    PY[Python specialist services]
  end
  B --> AUTH
  DEV --> AUTH
  EXT --> AUTH
  AI --> AUTH
  AUTH --> TEN
  TEN --> IAM
  TEN --> ENT
  IAM --> DOA
  ENT --> DOM
  DOA --> DOM
  DOM --> PG
  DOM --> AUD
  DOM --> OUT
  OUT --> REDIS
  REDIS --> WORKER
  WORKER --> ADAPTER
  WORKER --> PY
  PG --> AUD
~~~

## 2. Logical enterprise and identity model

~~~mermaid
erDiagram
  TENANT ||--o{ LEGAL_ENTITY : manages
  TENANT ||--o{ BUSINESS_UNIT : contains
  TENANT ||--o{ ORG_UNIT : defines
  TENANT ||--o{ BUSINESS_SITE : registers
  LEGAL_ENTITY ||--o{ BUSINESS_SITE : operates
  LEGAL_ENTITY ||--o{ BRANCH_REGISTRATION : registers
  BUSINESS_SITE ||--o{ OPERATIONAL_NODE : hosts
  LEGAL_ENTITY ||--o{ OPERATIONAL_NODE : owns_or_uses
  OPERATIONAL_NODE ||--o{ INVENTORY_LOCATION : contains_if_enabled
  ORG_UNIT ||--o{ ORG_UNIT : parent_child
  ORG_UNIT ||--o{ JOB_POSITION : defines
  JOB_POSITION ||--o{ POSITION_REPORTING_RELATION : subject
  JOB_POSITION ||--o{ POSITION_REPORTING_RELATION : manager
  JOB_POSITION ||--o{ POSITION_ASSIGNMENT : filled_by
  PERSON ||--o{ POSITION_ASSIGNMENT : assigned_to
  PERSON ||--o| USER_IDENTITY : may_link_to
  USER_IDENTITY ||--o{ TENANT_MEMBERSHIP : joins
  TENANT ||--o{ TENANT_MEMBERSHIP : contains
  POSITION_ASSIGNMENT ||--o{ POSITION_SCOPE_ASSIGNMENT : scoped_by
  POSITION_ASSIGNMENT ||--o{ AUTHORITY_DELEGATION : may_delegate
  DOA_POLICY ||--o{ DOA_RULE : contains
  DOA_POLICY ||--o{ APPROVAL_REQUEST : evaluated_by
  APPROVAL_REQUEST ||--o{ APPROVAL_STEP : requires
  APPROVAL_STEP ||--o{ APPROVAL_DECISION : decided_by
~~~

**Implementation note:** this is a conceptual ER model. The current PR #21 candidate adds only ORG_UNIT, JOB_POSITION, ORG_UNIT_MANAGER_ASSIGNMENT and POSITION_REPORTING_RELATION tables. People, employee, position assignment, DoA policy, approval and delegation tables remain future package work after schema reconciliation.

## 3. Effective-dated authority resolution

~~~mermaid
flowchart TD
  A[Incoming protected command] --> B[Authenticate token and session]
  B --> C{Identity active?}
  C -->|No| DENY[Fail closed]
  C -->|Yes| D[Resolve tenant membership and tenant lifecycle]
  D --> E{Membership and tenant active?}
  E -->|No| DENY
  E -->|Yes| F[Resolve target record under RLS]
  F --> G[Derive entity, site, org, cost centre, channel and risk]
  G --> H[Resolve person and effective position appointment]
  H --> I{Appointment required and valid?}
  I -->|No| DENY
  I -->|Yes| J[Resolve domain capability and target resource scope]
  J --> K[Resolve active DoA policy/rule version]
  K --> L[Check amount, currency, budget, cumulative limit and date]
  L --> M[Check SoD, conflict and delegation]
  M --> N{All controls pass?}
  N -->|No| DENY
  N -->|Yes| O[Return decision + policy/appointment snapshot]
  O --> P[Execute domain command transaction]
  P --> Q[Write aggregate, audit and outbox atomically]
~~~

## 4. Purchase-to-pay approval sequence

~~~mermaid
sequenceDiagram
  autonumber
  actor Requester
  participant ERP as ERP command/API
  participant Budget as Budget service
  participant DoA as Position/DoA resolver
  participant Approver as Authorized approver
  participant Receiving as Independent receiving
  participant Quality as Independent quality
  participant AP as Accounts Payable
  participant Treasury as Treasury/bank mandate
  Requester->>ERP: Submit requisition with entity/site/budget
  ERP->>Budget: Validate available and committed budget
  Budget-->>ERP: Budget result and snapshot
  ERP->>ERP: Create sourcing/technical/commercial evidence
  ERP->>DoA: Resolve approver chain for PO version
  DoA-->>ERP: Required steps and authority snapshots
  ERP->>Approver: Request approval
  Approver->>ERP: Decide with reason
  ERP->>ERP: Re-evaluate SoD/policy/version and persist decision
  ERP-->>Requester: PO may be issued only after all steps pass
  Receiving->>ERP: Record actual receipt independently
  Quality->>ERP: Accept/quarantine/reject as applicable
  AP->>ERP: Match PO + receipt + invoice
  Treasury->>ERP: Prepare payment under bank mandate
  Treasury->>ERP: Separate authorized bank release
~~~

## 5. Organization and position lifecycle

~~~mermaid
stateDiagram-v2
  [*] --> DraftUnit
  DraftUnit --> ActiveUnit: validated and approved setup
  ActiveUnit --> InactiveUnit: effective-dated closure
  ActiveUnit --> ActiveUnit: create child / position / reporting
  state Position {
    [*] --> Vacant
    Vacant --> Occupied: appointment effective
    Occupied --> Acting: approved acting appointment
    Acting --> Occupied: acting period ends
    Occupied --> Vacant: assignment ends
    Vacant --> Inactive: post abolished
  }
  state Appointment {
    [*] --> Pending
    Pending --> Active: separate authorized decision
    Pending --> Rejected
    Active --> Ended: end/transfer/revocation
    Active --> Superseded: new approved appointment
  }
~~~

## 6. Policy version and approval request state

~~~mermaid
stateDiagram-v2
  [*] --> DraftPolicy
  DraftPolicy --> UnderReview
  UnderReview --> Approved
  UnderReview --> Rejected
  Approved --> Active
  Active --> Superseded
  Active --> Suspended
  Active --> Expired
  Superseded --> [*]
  Suspended --> Active: new approval/effective period
  Expired --> [*]
  state ApprovalRequest {
    [*] --> Requested
    Requested --> AwaitingSteps
    AwaitingSteps --> PartiallyApproved
    PartiallyApproved --> ApprovedRequest
    AwaitingSteps --> RejectedRequest
    PartiallyApproved --> RejectedRequest
    AwaitingSteps --> ExpiredRequest
    ApprovedRequest --> Reopened: material transaction mutation
    Reopened --> AwaitingSteps
  }
~~~

## 7. Agent action boundary

~~~mermaid
flowchart TD
  IN[User request / source documents] --> AG[Agent runtime]
  AG --> READ[Allowlisted read-only domain APIs]
  READ --> EVID[Permission-filtered records + source evidence]
  EVID --> PROP[Draft/proposal with payload hash, reason and expiry]
  PROP --> GATE{Action risk and policy}
  GATE -->|Read / explain| OUT[Return cited answer]
  GATE -->|Draft only| DRAFT[Persist draft proposal]
  GATE -->|Approval-gated| HUMAN[Independent human approver]
  HUMAN --> DEC{Approve and authorize?}
  DEC -->|No| REJ[Reject / request changes]
  DEC -->|Yes| DOM[Domain service revalidates current state]
  DOM --> AUD[Audit + outbox + result]
  GATE -->|Prohibited A4| DENY[Refuse agent execution]
  DOM --> AUD
~~~

## 8. Legal-source lifecycle

~~~mermaid
flowchart LR
  DISC[Official authority discovery] --> CAP[Acquire full Gazette/directive]
  CAP --> HASH[Preserve bytes + SHA-256]
  HASH --> META[Register title, number, authority, articles, dates]
  META --> AMH[Controlling Amharic text]
  META --> TRANS[Authorized translation review]
  AMH --> OPINION[Qualified interpretation / applicability]
  TRANS --> OPINION
  OPINION --> APPROVAL[Independent professional approval]
  APPROVAL --> VERSION[Versioned legal rule snapshot]
  VERSION --> TEST[Independent legal and calculation vectors]
  TEST --> ACT[Separate customer activation decision]
  ACT --> RUN[Effective-dated execution with audit]
  DISC --> HOLD[REVIEW REQUIRED / hold if unresolved]
  HASH --> HOLD
  OPINION --> HOLD
~~~

## 9. Runtime package boundary

~~~mermaid
flowchart TB
  WP03[WP-03 Enterprise + tenant + org + identity + scope + DoA foundation]
  WP02[WP-02 Ethiopia legal sources and applicability]
  WP07[WP-07 Procurement consumes DoA]
  WP13[WP-13 Finance and Treasury consume entity/budget/approval]
  WP14[WP-14 People and Payroll consumes employee/appointment and legal sources]
  WP15[WP-15 Agents consume governed APIs/events]
  WP16[WP-16 end-to-end/security/recovery/UAT/release]
  WP02 --> WP03
  WP03 --> WP07
  WP03 --> WP13
  WP03 --> WP14
  WP03 --> WP15
  WP07 --> WP16
  WP13 --> WP16
  WP14 --> WP16
  WP15 --> WP16
~~~

The fixed WP-00–WP-16 order remains controlling. A diagram expresses dependency; it does not authorize package skipping.
