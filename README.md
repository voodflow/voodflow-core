## Voodflow Core

`voodflow/voodcore` contains shared, framework-agnostic building blocks used by Voodflow packages.

### Goals
- Provide stable **contracts** and **data structures** shared across packages.
- Avoid database/storage concerns (each package owns its own tables).
- Keep backwards-compatible, versioned APIs to support Filament v4 and v5 packages.

### Contents (initial)
- `Contracts/ExecutionLoggerInterface`: structured logging contract for execution traces.
- `DataTransferObjects/TestResult`: small DTO used by credential/connectivity tests.

