# CI workflows

`ci.yml` defines the local release sequence for GitHub Actions on Node 22.x: syntax validation, source manifest integrity, integration tests and documentation/runtime/security smoke gates. Branch and pull-request events run the same checks; manual dispatch is available.
