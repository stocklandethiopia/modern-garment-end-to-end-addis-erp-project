# `.github/` — continuous integration and repository automation

**Owner:** Release Engineering  
**Application version:** 0.3.0

## Workflow inventory

| File | Trigger | Verification jobs |
|---|---|---|
| `workflows/ci.yml` | Push to `main`, feature/release branches, pull request to `main`, manual dispatch | Node setup, source syntax, build manifest, integration suite, documentation, HTTP, PWA and security gates |

The workflow uses read-only repository contents permission. It runs the project's four reproducible local release gates and publishes the normal GitHub Actions log and job result for each run.

## CI command sequence

```bash
npm run check
npm run verify:manifest
npm test
npm run release:gate
```

## Related engineering documents

- [Release gates](../docs/RELEASE_GATES.md)
- [Test strategy](../docs/TEST_STRATEGY.md)
- [Verification report](../docs/VERIFICATION_REPORT.md)
