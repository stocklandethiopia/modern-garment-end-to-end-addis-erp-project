# Release evidence record

**Release version:** [version/tag]  
**Branch:** [branch]  
**Commit SHA:** [full SHA]  
**Prepared:** YYYY-MM-DD  
**Environment:** [OS / runtime / database]

## Verification results

| Gate | Command / URL | Result | Evidence |
|---|---|---|---|
| Syntax | `npm run check` | [result] | [log or CI job] |
| Integration | `npm test` | [result] | [log or CI job] |
| Documentation/runtime | `npm run release:gate` | [result] | [log or CI job] |
| File integrity | `npm run verify:manifest` | [result] | [manifest/hash] |
| Security | [scanner/review] | [result] | [report] |
| Backup/restore | [procedure] | [result] | [restore record] |
| Business acceptance | [scenario set] | [result] | [signed result] |

## Scope and acceptance evidence

List the delivered modules, requirement IDs, source artifacts, acceptance scenarios and evidence links. Record any accepted operating policy as a documented decision with its owner and review date.

## Release record

- Local validation result: [result]
- CI run: [URL]
- Release tag: [tag]
- Artifact checksum: [SHA-256]
- Documentation revision: [revision]

**Release owner:** [authorized role]  
**Date:** YYYY-MM-DD
