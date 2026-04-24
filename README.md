## Voodflow Core

`voodflow/voodcore` contains shared, framework-agnostic building blocks used by Voodflow packages.

### Goals
- Provide stable **contracts** and **data structures** shared across packages.
- Avoid database/storage concerns (each package owns its own tables).
- Keep backwards-compatible, versioned APIs to support Filament v4 and v5 packages.

### Contents (initial)
- `Contracts/ExecutionLoggerInterface`: structured logging contract for execution traces.
- `DataTransferObjects/TestResult`: small DTO used by credential/connectivity tests.
- `Contracts/OAuth2FlowInterface` + `Support/Pkce`: reusable OAuth2/PKCE primitives.
- `Contracts/HttpClientInterface` + `Contracts/CacheStoreInterface`: framework adapters surface (Laravel implementations live in plugins).
- `Services/OAuth2Flow`: framework-agnostic OAuth2 implementation (requires Http/Cache adapters).
- `Services/SmtpTester`: pure-PHP SMTP connectivity tester used by plugins.

