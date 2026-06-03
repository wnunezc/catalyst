# Responsibility Map Audit

Date: 2026-06-02
Scope: PHP runtime responsibilities and framework documentation alignment.

## Inputs

- Inline PHP class and method docblocks.
- Runtime module catalog.
- Runtime inventory.
- CLI command catalog.
- Route list.
- Existing `/docs` Markdown.

## Verification Commands

- `php public/cli.php docs:inventory --json`
- `php public/cli.php docs:sync-runtime --stdout`
- `php public/cli.php route:list --json`
- `php public/cli.php inspect:lint`
- `php public/cli.php route:lint`

## Scope Rules

- Runtime logic is read-only.
- Documentation may be updated after classification.
- Obsolete docs are classified before removal.

## Output Files

- `docs/audits/documentation/2026-06-02-duplication-candidates.md`
- `docs/audits/documentation/2026-06-02-docs-classification.md`
- `docs/audits/documentation/2026-06-02-docs-reconciliation-report.md`

## Class Responsibility Inventory

The table below was generated from PHP docblocks and is used as the source for duplicate responsibility review.

| File | Symbol | Package | Responsibility |
|---|---|---|---|
| `Repository/App/Repositories/UserProfileRepository.php` | `UserProfileRepository` | `App\Repositories` | Reads profile data and profile counts through the active tenant boundary. |
| `Repository/App/Services/ApplicationEntryService.php` | `ApplicationEntryService` | `App\Services` | Maps project entry configuration and authentication state to the first route users should reach. |
| `Repository/App/Services/UserProfileService.php` | `UserProfileService` | `App\Services` | Combines the current user payload with profile repository data for presentation. |
| `Repository/App/Support/PublicSurface/Controllers/PublicPageController.php` | `PublicPageController` | `App\Support\PublicSurface\Controllers` | Prepares shared public layout context, navigation, and versioned surface assets. |
| `Repository/App/Support/PublicSurface/Support/PublicDemoCatalog.php` | `PublicDemoCatalog` | `App\Support\PublicSurface\Support` | Supplies localized content arrays for home, landing, store, and dashboard surfaces. |
| `Repository/App/Surface/Account/Controllers/AccountCenterController.php` | `AccountCenterController` | `App\Surface\Account\Controllers` | Renders account profile, MFA, recovery support and activity screens for signed-in users. |
| `Repository/App/Surface/Account/Controllers/AccountRecoveryAdminController.php` | `AccountRecoveryAdminController` | `App\Surface\Account\Controllers` | Lists support recovery requests, shows request details and records approve/reject decisions. |
| `Repository/App/Surface/Account/Controllers/AccountRecoveryController.php` | `AccountRecoveryController` | `App\Surface\Account\Controllers` | Renders guest recovery forms, submits support requests and consumes MFA reset tokens. |
| `Repository/App/Surface/Account/Repositories/AccountRecoveryRepository.php` | `AccountRecoveryRepository` | `App\Surface\Account\Repositories` | Encapsulates tenant-scoped storage access for account recovery workflows. |
| `Repository/App/Surface/Account/Requests/MfaRecoveryRequest.php` | `MfaRecoveryRequest` | `App\Surface\Account\Requests` | Normalizes and validates the email address used to request an MFA reset. |
| `Repository/App/Surface/Account/Requests/SupportRecoveryRequest.php` | `SupportRecoveryRequest` | `App\Surface\Account\Requests` | Normalizes request type, known email, alternate email and user message for support review. |
| `Repository/App/Surface/Account/Services/AccountDashboardService.php` | `AccountDashboardService` | `App\Surface\Account\Services` | Combines current user, MFA and recovery activity state into dashboard metrics. |
| `Repository/App/Surface/Account/Services/AccountRecoveryService.php` | `AccountRecoveryService` | `App\Surface\Account\Services` | Applies account recovery business rules, persists request state and sends recovery email. |
| `Repository/App/Surface/Account/Services/AccountSecurityService.php` | `AccountSecurityService` | `App\Surface\Account\Services` | Reports MFA state, open recovery request counts and recent recovery activity. |
| `Repository/App/Surface/Account/Support/AccountShellViewModel.php` | `AccountShellViewModel` | `App\Surface\Account\Support` | Provides authenticated and guest shell metadata, navigation, CSRF, CSP and appearance data. |
| `Repository/App/Surface/Dashboard/Controllers/DashboardController.php` | `DashboardController` | `App\Surface\Dashboard\Controllers` | Renders the authenticated account shell, presents a guest gateway for anonymous users, and exposes dashboard demo data. |
| `Repository/App/Surface/Home/Controllers/HomeController.php` | `HomeController` | `App\Surface\Home\Controllers` | Resolves the application root target, renders the home demo page, and exposes its companion payload. |
| `Repository/App/Surface/Landing/Controllers/LandingController.php` | `LandingController` | `App\Surface\Landing\Controllers` | Renders the landing demo page, publishes its catalog-backed payload, and normalizes legacy landing aliases. |
| `Repository/App/Surface/Store/Controllers/StoreController.php` | `StoreController` | `App\Surface\Store\Controllers` | Renders the store catalog demo page, publishes its companion payload, and normalizes legacy store aliases. |
| `app/Kernel.php` | `Kernel` | `Catalyst` | Bootstraps runtime services, loads routes and dispatches HTTP requests. |
| `app/Entities/ApiToken.php` | `ApiToken` | `Catalyst\Entities` | Maps token metadata, scopes, expiration, revocation, and hidden token hashes for tenant users. |
| `app/Entities/AuditLogEntry.php` | `AuditLogEntry` | `Catalyst\Entities` | Maps actor, tenant, request, resource, and payload fields written by audit logging. |
| `app/Entities/AutomationExecutionLog.php` | `AutomationExecutionLog` | `Catalyst\Entities` | Maps trigger context, execution status, messages, and results for tenant automation runs. |
| `app/Entities/AutomationRule.php` | `AutomationRule` | `Catalyst\Entities` | Maps rule triggers, conditions, actions, schedules, effective windows, and audit metadata. |
| `app/Entities/CatalogDefinition.php` | `CatalogDefinition` | `Catalyst\Entities` | Maps catalog identity, labels, descriptions, audit fields, and optimistic locking state. |
| `app/Entities/CatalogItem.php` | `CatalogItem` | `Catalyst\Entities` | Maps catalog item labels, ordering, availability windows, metadata, and audit state. |
| `app/Entities/ContentVersion.php` | `ContentVersion` | `Catalyst\Entities` | Maps resource version numbers, snapshots, diffs, summaries, and actor metadata. |
| `app/Entities/DeploymentRun.php` | `DeploymentRun` | `Catalyst\Entities` | Maps deployment profile runs, status, artifact paths, remote paths, summaries, and timing. |
| `app/Entities/DocumentArtifact.php` | `DocumentArtifact` | `Catalyst\Entities` | Maps generated document files, storage locations, checksums, payload snapshots, and archive state. |
| `app/Entities/DocumentTemplate.php` | `DocumentTemplate` | `Catalyst\Entities` | Maps template identity, format, variable schema, sample payload, body content, and lock state. |
| `app/Entities/EventEnvelope.php` | `EventEnvelope` | `Catalyst\Entities` | Carries event name, payload, metadata, identifier, and occurrence timestamp across dispatch boundaries. |
| `app/Entities/FeatureFlagOverride.php` | `FeatureFlagOverride` | `Catalyst\Entities` | Maps flag keys, target subjects, enabled state, notes, and audit metadata. |
| `app/Entities/IdempotencyKey.php` | `IdempotencyKey` | `Catalyst\Entities` | Maps scoped request fingerprints, processing status, stored outcomes, and completion timestamps. |
| `app/Entities/MediaItem.php` | `MediaItem` | `Catalyst\Entities` | Maps uploaded file identity, storage location, public URL, MIME metadata, archive state, and lock state. |
| `app/Entities/MetadataFieldDefinition.php` | `MetadataFieldDefinition` | `Catalyst\Entities` | Maps resource field schema, validation flags, catalog options, display settings, and lock state. |
| `app/Entities/MetadataFieldValue.php` | `MetadataFieldValue` | `Catalyst\Entities` | Maps resource metadata values across supported scalar, date, datetime, and media-backed types. |
| `app/Entities/NotificationDispatch.php` | `NotificationDispatch` | `Catalyst\Entities` | Carries notification recipient, type, title, body, and metadata through dispatch workflows. |
| `app/Entities/QueuedJobRecord.php` | `QueuedJobRecord` | `Catalyst\Entities` | Carries queue metadata, decoded payload, retry state, reservation state, and creation timestamps. |
| `app/Entities/RecordClaim.php` | `RecordClaim` | `Catalyst\Entities` | Maps claim tokens, claim owners, expiration, release state, metadata, and lock state. |
| `app/Entities/ReportRun.php` | `ReportRun` | `Catalyst\Entities` | Maps report criteria, output attachments, queue linkage, execution status, and timing. |
| `app/Entities/ResourceAttachment.php` | `ResourceAttachment` | `Catalyst\Entities` | Maps media or document attachments, purpose, primary marker, detach state, and audit metadata. |
| `app/Entities/ScheduledTask.php` | `ScheduledTask` | `Catalyst\Entities` | Carries cron expression, queue target, serialized job payload, and human-readable task metadata. |
| `app/Entities/TimelineEvent.php` | `TimelineEvent` | `Catalyst\Entities` | Maps tenant resource milestones, event labels, metadata, and occurrence timestamps. |
| `app/Entities/UserProfile.php` | `UserProfile` | `Catalyst\Entities` | Maps extended user identity, contact, organization, audit, and optimistic locking fields. |
| `app/Entities/WorkflowInstance.php` | `WorkflowInstance` | `Catalyst\Entities` | Maps workflow definition, target resource, current state, context payload, and audit metadata. |
| `app/Entities/WorkflowTransition.php` | `WorkflowTransition` | `Catalyst\Entities` | Maps transition keys, state movement, notes, metadata, actor, and occurrence timestamp. |
| `app/Framework/Admin/Crud/CrudAssetPublisher.php` | `CrudAssetPublisher` | `Catalyst\Framework\Admin\Crud` | Copies generated front CSS and JavaScript files into the public work asset directories. |
| `app/Framework/Admin/Crud/CrudBlueprintFactory.php` | `CrudBlueprintFactory` | `Catalyst\Framework\Admin\Crud` | Validates scaffold input and composes module, entity, route, schema, migration, and file metadata. |
| `app/Framework/Admin/Crud/CrudFieldDefinitionParser.php` | `CrudFieldDefinitionParser` | `Catalyst\Framework\Admin\Crud` | Converts user-provided field specs into normalized field metadata for scaffold generation. |
| `app/Framework/Admin/Crud/CrudFileFactory.php` | `CrudFileFactory` | `Catalyst\Framework\Admin\Crud` | Renders controller, request, entity, migration, view, route, and module files from a CRUD blueprint. |
| `app/Framework/Admin/Crud/CrudScaffoldService.php` | `CrudScaffoldService` | `Catalyst\Framework\Admin\Crud` | Builds a CRUD blueprint, writes generated files, publishes work assets, and returns creation metadata. |
| `app/Framework/Admin/Crud/CrudSchemaBuilder.php` | `CrudSchemaBuilder` | `Catalyst\Framework\Admin\Crud` | Converts parsed field definitions into form, grid, validation, migration, and search metadata. |
| `app/Framework/Admin/Form/FormBuilder.php` | `FormBuilder` | `Catalyst\Framework\Admin\Form` | Normalizes form configuration, fields, sections, actions, model values, and HTML attributes for templates. |
| `app/Framework/Admin/Grid/DataGrid.php` | `DataGrid` | `Catalyst\Framework\Admin\Grid` | Coordinates grid configuration, request state, provider results, presentation metadata, and CSV/XLS responses. |
| `app/Framework/Admin/Grid/DataGridBulkActionNormalizer.php` | `DataGridBulkActionNormalizer` | `Catalyst\Framework\Admin\Grid` | Converts bulk action definitions into form-ready metadata while preserving grid query state. |
| `app/Framework/Admin/Grid/DataGridColumnNormalizer.php` | `DataGridColumnNormalizer` | `Catalyst\Framework\Admin\Grid` | Builds render-ready column metadata, including labels, alignment classes, and sort links. |
| `app/Framework/Admin/Grid/DataGridCsvExporter.php` | `DataGridCsvExporter` | `Catalyst\Framework\Admin\Grid` | Writes headers and rows through PHP CSV handling so exported values are escaped consistently. |
| `app/Framework/Admin/Grid/DataGridExportNormalizer.php` | `DataGridExportNormalizer` | `Catalyst\Framework\Admin\Grid` | Converts export format configuration and print support into render-ready toolbar actions. |
| `app/Framework/Admin/Grid/DataGridFilterNormalizer.php` | `DataGridFilterNormalizer` | `Catalyst\Framework\Admin\Grid` | Converts filter definitions and request state into render-ready filter controls. |
| `app/Framework/Admin/Grid/DataGridHtmlExportRenderer.php` | `DataGridHtmlExportRenderer` | `Catalyst\Framework\Admin\Grid` | Keeps export markup in templates instead of assembling HTML inside grid coordination code. |
| `app/Framework/Admin/Grid/DataGridPaginationBuilder.php` | `DataGridPaginationBuilder` | `Catalyst\Framework\Admin\Grid` | Calculates page bounds, result ranges, per-page options, and navigation URLs. |
| `app/Framework/Admin/Grid/DataGridRowActionNormalizer.php` | `DataGridRowActionNormalizer` | `Catalyst\Framework\Admin\Grid` | Resolves visibility, labels, URLs, confirmation text, and styling for actions on a row. |
| `app/Framework/Admin/Grid/DataGridRowNormalizer.php` | `DataGridRowNormalizer` | `Catalyst\Framework\Admin\Grid` | Maps raw provider rows into cells, row keys, row actions, and sanitized export values. |
| `app/Framework/Admin/Grid/DataGridStateResolver.php` | `DataGridStateResolver` | `Catalyst\Framework\Admin\Grid` | Extracts and validates pagination, sorting, search, filter, and raw query state for providers. |
| `app/Framework/Admin/Grid/DataGridTextFormatter.php` | `DataGridTextFormatter` | `Catalyst\Framework\Admin\Grid` | Generates human-readable labels and filesystem-safe export slugs from configured keys. |
| `app/Framework/Admin/Grid/DataGridUrlBuilder.php` | `DataGridUrlBuilder` | `Catalyst\Framework\Admin\Grid` | Applies query overrides and generates links for pagination, sorting, exports, and actions. |
| `app/Framework/Api/ApiCatalog.php` | `ApiCatalog` | `Catalyst\Framework\Api` | Lists API endpoints with their HTTP method, path, permission gate, and human description. |
| `app/Framework/Api/ApiTokenManager.php` | `ApiTokenManager` | `Catalyst\Framework\Api` | Enforces user existence, generates plain-text secrets, persists hashed token records, and updates token usage state. |
| `app/Framework/Api/ApiTokenRepository.php` | `ApiTokenRepository` | `Catalyst\Framework\Api` | Searches, resolves, and revokes API tokens while constraining queries to the current tenant. |
| `app/Framework/Appearance/PlatformAppearanceManager.php` | `PlatformAppearanceManager` | `Catalyst\Framework\Appearance` | Normalizes appearance configuration, exposes view models, stores brand assets, and constrains customizer values. |
| `app/Framework/Argument/Argument.php` | `Argument` | `Catalyst\Framework\Argument` | Maintains parser, validator, parsed bag, and optional option schema for command-line consumers. |
| `app/Framework/Argument/ArgumentBag.php` | `ArgumentBag` | `Catalyst\Framework\Argument` | Provides lookup, existence checks, counts, and array conversion for parsed command-line input. |
| `app/Framework/Argument/ArgumentParser.php` | `ArgumentParser` | `Catalyst\Framework\Argument` | Recognizes long options, short options, combined short flags, option values, and positional parameters. |
| `app/Framework/Argument/Option.php` | `Option` | `Catalyst\Framework\Argument` | Stores option names, value/default state, required metadata, description, and value acceptance rules. |
| `app/Framework/Argument/Parameter.php` | `Parameter` | `Catalyst\Framework\Argument` | Stores parameter position, current/default value, required metadata, name, and description. |
| `app/Framework/Argument/Validator.php` | `Validator` | `Catalyst\Framework\Argument` | Tracks validation errors, checks required inputs, validates scalar types, and casts option values. |
| `app/Framework/Attachment/AttachmentManager.php` | `AttachmentManager` | `Catalyst\Framework\Attachment` | Attach, replace, list and detach resource-owned assets while preserving reference safety. |
| `app/Framework/Attachment/AttachmentRepository.php` | `AttachmentRepository` | `Catalyst\Framework\Attachment` | Query attachment listings, reporting rows and active asset references for the current tenant. |
| `app/Framework/Audit/AuditLogManager.php` | `AuditLogManager` | `Catalyst\Framework\Audit` | Build audit context, sanitize payloads and persist tenant-aware audit records. |
| `app/Framework/Audit/AuditLogRepository.php` | `AuditLogRepository` | `Catalyst\Framework\Audit` | Search, decode and filter audit records without exposing write-side audit logic. |
| `app/Framework/Auth/AuthInputGuard.php` | `AuthInputGuard` | `Catalyst\Framework\Auth` | Guard redirect targets, auth tokens, MFA codes and password-policy checks at auth boundaries. |
| `app/Framework/Auth/AuthManager.php` | `AuthManager` | `Catalyst\Framework\Auth` | Orchestrate user authentication state through SessionManager, RememberMe and tenant-aware user context. |
| `app/Framework/Auth/MfaManager.php` | `MfaManager` | `Catalyst\Framework\Auth` | Generate MFA credentials, verify submitted codes and hash backup codes for persistence. |
| `app/Framework/Auth/OAuthManager.php` | `OAuthManager` | `Catalyst\Framework\Auth` | Create provider redirects, validate callback state and normalize provider users. |
| `app/Framework/Auth/RememberMe.php` | `RememberMe` | `Catalyst\Framework\Auth` | Issue, resolve and invalidate remember-me tokens without storing raw token values. |
| `app/Framework/Auth/TokenRepository.php` | `TokenRepository` | `Catalyst\Framework\Auth` | Create, consume and invalidate user recovery tokens without persisting raw token values. |
| `app/Framework/Auth/UserDirectoryRepository.php` | `UserDirectoryRepository` | `Catalyst\Framework\Auth` | Provide tenant-scoped user summaries, select options and admin listings. |
| `app/Framework/Auth/UserProvider.php` | `UserProvider` | `Catalyst\Framework\Auth` | Resolve users, manage credentials, link OAuth accounts and persist MFA state. |
| `app/Framework/Auth/OAuth/GitHubProvider.php` | `GitHubProvider` | `Catalyst\Framework\Auth\OAuth` | Supply GitHub endpoints, scopes, headers, error handling and normalized OAuth users. |
| `app/Framework/Auth/OAuth/GoogleProvider.php` | `GoogleProvider` | `Catalyst\Framework\Auth\OAuth` | Supply Google endpoints, scopes, error handling and normalized OAuth users. |
| `app/Framework/Auth/OAuth/OAuthUser.php` | `OAuthUser` | `Catalyst\Framework\Auth\OAuth` | Expose provider identity, display name, email and raw payload through one resource-owner interface. |
| `app/Framework/Authorization/AbilitySubject.php` | `AbilitySubject` | `Catalyst\Framework\Authorization` | Represents the subject passed to resource-level authorization checks. |
| `app/Framework/Authorization/Gate.php` | `Gate` | `Catalyst\Framework\Authorization` | Evaluates authorization abilities through registered closures and policy classes. |
| `app/Framework/Authorization/PermissionRegistry.php` | `PermissionRegistry` | `Catalyst\Framework\Authorization` | Bridges module permission metadata with Gate and RoleRepository checks. |
| `app/Framework/Authorization/Policy.php` | `Policy` | `Catalyst\Framework\Authorization` | Lets concrete policies short-circuit ability checks before can* methods run. |
| `app/Framework/Authorization/RbacAuditLogger.php` | `RbacAuditLogger` | `Catalyst\Framework\Authorization` | Normalizes role and permission changes into audit operations. |
| `app/Framework/Authorization/RbacCacheInvalidator.php` | `RbacCacheInvalidator` | `Catalyst\Framework\Authorization` | Clears user-scoped and global cache entries after RBAC mutations. |
| `app/Framework/Authorization/RbacSortResolver.php` | `RbacSortResolver` | `Catalyst\Framework\Authorization` | Constrains user-provided sort options to repository-approved SQL fragments. |
| `app/Framework/Authorization/ResourcePolicy.php` | `ResourcePolicy` | `Catalyst\Framework\Authorization` | Authorizes AbilitySubject instances through resource permission definitions. |
| `app/Framework/Authorization/RoleRepository.php` | `RoleRepository` | `Catalyst\Framework\Authorization` | Provides the database boundary for role and permission reads and mutations. |
| `app/Framework/Automation/AutomationManager.php` | `AutomationManager` | `Catalyst\Framework\Automation` | Creates, updates, transitions and executes automation rules for events and schedules. |
| `app/Framework/Automation/AutomationRuleRepository.php` | `AutomationRuleRepository` | `Catalyst\Framework\Automation` | Queries tenant-scoped automation rules, schedules and execution records. |
| `app/Framework/Automation/Jobs/RunScheduledAutomationRulesJob.php` | `RunScheduledAutomationRulesJob` | `Catalyst\Framework\Automation\Jobs` | Delegates scheduled automation execution to the automation manager. |
| `app/Framework/Cache/ArrayCacheStore.php` | `ArrayCacheStore` | `Catalyst\Framework\Cache` | Provides ephemeral cache storage with optional expiration for the current process. |
| `app/Framework/Cache/BootstrapCacheManager.php` | `BootstrapCacheManager` | `Catalyst\Framework\Cache` | Loads, writes and clears bootstrap cache artifacts atomically. |
| `app/Framework/Cache/CacheManager.php` | `CacheManager` | `Catalyst\Framework\Cache` | Resolves cache drivers from runtime settings and forwards cache operations. |
| `app/Framework/Cache/CacheSettings.php` | `CacheSettings` | `Catalyst\Framework\Cache` | Supplies cache defaults, environment paths and feature enablement decisions. |
| `app/Framework/Cache/CacheStoreInterface.php` | `CacheStoreInterface` | `Catalyst\Framework\Cache` | Standardizes cache reads, writes, eviction and resolver-backed retrieval. |
| `app/Framework/Cache/FileCacheStore.php` | `FileCacheStore` | `Catalyst\Framework\Cache` | Reads, writes and evicts namespaced filesystem cache entries. |
| `app/Framework/Cache/NullCacheStore.php` | `NullCacheStore` | `Catalyst\Framework\Cache` | Disables caching while preserving the cache store contract. |
| `app/Framework/Catalog/CatalogItemAvailabilityDecorator.php` | `CatalogItemAvailabilityDecorator` | `Catalyst\Framework\Catalog` | Normalizes item rows and derives whether each item is currently selectable. |
| `app/Framework/Catalog/CatalogManager.php` | `CatalogManager` | `Catalyst\Framework\Catalog` | Applies catalog mutations and coordinates persistence, temporal rules and versions. |
| `app/Framework/Catalog/CatalogOptionMapBuilder.php` | `CatalogOptionMapBuilder` | `Catalyst\Framework\Catalog` | Filters catalog rows and formats stable key-to-label option maps. |
| `app/Framework/Catalog/CatalogRepository.php` | `CatalogRepository` | `Catalyst\Framework\Catalog` | Provides catalog persistence, filtered lookups and normalized option data. |
| `app/Framework/Cli/AbstractCommand.php` | `AbstractCommand` | `Catalyst\Framework\Cli` | Provides shared option defaults, terminal output helpers and interactive prompts for concrete commands. |
| `app/Framework/Cli/CliKernel.php` | `CliKernel` | `Catalyst\Framework\Cli` | Parses argv, resolves commands, renders help and discovers application surface commands. |
| `app/Framework/Cli/CliRouteLoader.php` | `CliRouteLoader` | `Catalyst\Framework\Cli` | Loads global, API, framework and application route files in kernel-compatible order. |
| `app/Framework/Cli/CommandInterface.php` | `CommandInterface` | `Catalyst\Framework\Cli` | Defines command identity, help metadata, argument schema and execution entrypoint. |
| `app/Framework/Cli/CommandRegistry.php` | `CommandRegistry` | `Catalyst\Framework\Cli` | Stores command instances by name and exposes lookup operations for the CLI kernel. |
| `app/Framework/Cli/ScaffoldManager.php` | `ScaffoldManager` | `Catalyst\Framework\Cli` | Normalizes names, renders stubs and writes generated framework artifacts. |
| `app/Framework/Cli/TerminalStyle.php` | `TerminalStyle` | `Catalyst\Framework\Cli` | Wraps CLI output text with ANSI color sequences when supported. |
| `app/Framework/Cli/Commands/ApiTokensSmokeCommand.php` | `ApiTokensSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the api-tokens:smoke command to Exercise API token ownership, revocation and FK enforcement on the live schema. |
| `app/Framework/Cli/Commands/AttachmentsListCommand.php` | `AttachmentsListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the attachments:list command to List canonical resource attachments for one resource_key + record_id pair. |
| `app/Framework/Cli/Commands/AttachmentsSmokeCommand.php` | `AttachmentsSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the attachments:smoke command to Exercise the canonical PA-06 attachment contract over media, document artifacts, replace and detach flows. |
| `app/Framework/Cli/Commands/AutomationMvcRegressionCommand.php` | `AutomationMvcRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the automation:mvc-regression command to Verify Automation MVC separation without changing its public routes. |
| `app/Framework/Cli/Commands/CacheBuildCommand.php` | `CacheBuildCommand` | `Catalyst\Framework\Cli\Commands` | Runs the cache:build command to Build configured cache artifacts without changing activation flags. |
| `app/Framework/Cli/Commands/CacheClearCommand.php` | `CacheClearCommand` | `Catalyst\Framework\Cli\Commands` | Runs the cache:clear command to Clear route, bootstrap and application cache artifacts. |
| `app/Framework/Cli/Commands/CatalogsSmokeCommand.php` | `CatalogsSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the catalogs:smoke command to Exercise canonical PA-11 catalog CRUD plus metadata-driven form/grid consumption. |
| `app/Framework/Cli/Commands/ClaimsListCommand.php` | `ClaimsListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the claims:list command to List reusable record claims with current status. |
| `app/Framework/Cli/Commands/ClaimsReleaseCommand.php` | `ClaimsReleaseCommand` | `Catalyst\Framework\Cli\Commands` | Runs the claims:release command to Release one reusable record claim. |
| `app/Framework/Cli/Commands/ConcurrencySmokeCommand.php` | `ConcurrencySmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the concurrency:smoke command to Exercise optimistic locking plus claim reclaim on the canonical PA-01 runtime layer. |
| `app/Framework/Cli/Commands/ConfigSecretsSyncCommand.php` | `ConfigSecretsSyncCommand` | `Catalyst\Framework\Cli\Commands` | Runs the config:secrets:sync command to Move managed secret keys out of public JSON config into the companion secret store. |
| `app/Framework/Cli/Commands/ConfigShowCommand.php` | `ConfigShowCommand` | `Catalyst\Framework\Cli\Commands` | Runs the config:show command to Display effective JSON-backed configuration with sensitive values redacted. |
| `app/Framework/Cli/Commands/DeployListCommand.php` | `DeployListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the deploy:list command to List deployment profiles and recent runs. |
| `app/Framework/Cli/Commands/DeployRunCommand.php` | `DeployRunCommand` | `Catalyst\Framework\Cli\Commands` | Runs the deploy:run command to Execute the formal deployment pipeline for a configured profile. |
| `app/Framework/Cli/Commands/DevToolsDisableCommand.php` | `DevToolsDisableCommand` | `Catalyst\Framework\Cli\Commands` | Runs the devtools:disable command to Disable debug-oriented DevTools runtime flags in app and logging config. |
| `app/Framework/Cli/Commands/DocsInventoryCommand.php` | `DocsInventoryCommand` | `Catalyst\Framework\Cli\Commands` | Runs the docs:inventory command to Generate the symbol, template and script inventory used by the documentation contract. |
| `app/Framework/Cli/Commands/DocsSyncRuntimeCommand.php` | `DocsSyncRuntimeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the docs:sync-runtime command to Generate living runtime module documentation from registries, inspector, harness and lint. |
| `app/Framework/Cli/Commands/DocumentsMvcRegressionCommand.php` | `DocumentsMvcRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the documents:mvc-regression command to Verify Documents MVC separation without changing its public routes. |
| `app/Framework/Cli/Commands/ExportDevelopmentOverlayCommand.php` | `ExportDevelopmentOverlayCommand` | `Catalyst\Framework\Cli\Commands` | Runs the dev:export-overlay command to Export the live auth/RBAC development snapshot to boot-core/database/create-catalyst-db.development.sql. |
| `app/Framework/Cli/Commands/FeatureFlagsListCommand.php` | `FeatureFlagsListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the feature-flags:list command to List feature flags with default and effective runtime state. |
| `app/Framework/Cli/Commands/FeatureFlagsSetCommand.php` | `FeatureFlagsSetCommand` | `Catalyst\Framework\Cli\Commands` | Runs the feature-flags:set command to Set the default state of a mutable feature flag. |
| `app/Framework/Cli/Commands/FixturesAuthCommand.php` | `FixturesAuthCommand` | `Catalyst\Framework\Cli\Commands` | Runs the fixtures:auth command to Inspect, apply, snapshot and mutate official auth/RBAC fixtures for development and smoke flows. |
| `app/Framework/Cli/Commands/GeoSmokeCommand.php` | `GeoSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the geo:smoke command to Exercise canonical PA-07 geo normalization, distance and bounding-box semantics. |
| `app/Framework/Cli/Commands/HelpCommand.php` | `HelpCommand` | `Catalyst\Framework\Cli\Commands` | Lists registered CLI commands and their short descriptions for terminal discovery. |
| `app/Framework/Cli/Commands/I18nInitLocaleCommand.php` | `I18nInitLocaleCommand` | `Catalyst\Framework\Cli\Commands` | Runs the i18n:init-locale command to Initialize a locale by cloning the English catalog structure. |
| `app/Framework/Cli/Commands/I18nStatusCommand.php` | `I18nStatusCommand` | `Catalyst\Framework\Cli\Commands` | Runs the i18n:status command to List locales and report translation coverage against English base catalogs. |
| `app/Framework/Cli/Commands/I18nSyncCommand.php` | `I18nSyncCommand` | `Catalyst\Framework\Cli\Commands` | Runs the i18n:sync command to Backfill missing translation keys from English without overwriting existing translations. |
| `app/Framework/Cli/Commands/IdempotencySmokeCommand.php` | `IdempotencySmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the idempotency:smoke command to Exercise canonical PA-12 replay, in-progress and conflict handling over persisted idempotency keys. |
| `app/Framework/Cli/Commands/InspectHarnessCommand.php` | `InspectHarnessCommand` | `Catalyst\Framework\Cli\Commands` | Runs the inspect:harness command to Inspect the per-module runtime harness matrix over real routes, assets and guards. |
| `app/Framework/Cli/Commands/InspectLintCommand.php` | `InspectLintCommand` | `Catalyst\Framework\Cli\Commands` | Runs the inspect:lint command to Run structural framework lint on modules, registries, guards and work assets. |
| `app/Framework/Cli/Commands/InspectModuleCommand.php` | `InspectModuleCommand` | `Catalyst\Framework\Cli\Commands` | Runs the inspect:module command to Inspect one module in detail by key, slug or name. |
| `app/Framework/Cli/Commands/InspectModulesCommand.php` | `InspectModulesCommand` | `Catalyst\Framework\Cli\Commands` | Runs the inspect:modules command to Inspect registered modules, metadata, routes, assets and registry coverage. |
| `app/Framework/Cli/Commands/KeyGenerateCommand.php` | `KeyGenerateCommand` | `Catalyst\Framework\Cli\Commands` | Runs the key:generate command to Generate a new APP_KEY and persist it to .env plus the managed secret config store. |
| `app/Framework/Cli/Commands/MakeCommandCommand.php` | `MakeCommandCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:command command to Scaffold an auto-discovered CLI command in Repository/App/Surface/{Module}/Commands/. |
| `app/Framework/Cli/Commands/MakeControllerCommand.php` | `MakeControllerCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:controller command to Scaffold a new Controller in Repository/App/Surface/{Module}/Controllers/. |
| `app/Framework/Cli/Commands/MakeCrudCommand.php` | `MakeCrudCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:crud command to Scaffold an administrative CRUD module on top of the framework form builder and datagrid. |
| `app/Framework/Cli/Commands/MakeMiddlewareCommand.php` | `MakeMiddlewareCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:middleware command to Scaffold a new middleware in Repository/App/Middleware/. |
| `app/Framework/Cli/Commands/MakeMigrationCommand.php` | `MakeMigrationCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:migration command to Scaffold a new anonymous migration in boot-core/database/migrations/. |
| `app/Framework/Cli/Commands/MakeModelCommand.php` | `MakeModelCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:model command to Scaffold a new Model in Repository/App/Models/. |
| `app/Framework/Cli/Commands/MakeModuleCommand.php` | `MakeModuleCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:module command to Scaffold a full module structure plus module manifest in Repository/{App\|Framework}/. |
| `app/Framework/Cli/Commands/MakePolicyCommand.php` | `MakePolicyCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:policy command to Scaffold a Policy class in Repository/App/Surface/{Module}/Policies/. |
| `app/Framework/Cli/Commands/MakeRequestCommand.php` | `MakeRequestCommand` | `Catalyst\Framework\Cli\Commands` | Runs the make:request command to Scaffold a FormRequest class in Repository/App/Surface/{Module}/Requests/. |
| `app/Framework/Cli/Commands/MediaMvcRegressionCommand.php` | `MediaMvcRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the media:mvc-regression command to Verify Media request and presentation boundaries. |
| `app/Framework/Cli/Commands/MigrateCommand.php` | `MigrateCommand` | `Catalyst\Framework\Cli\Commands` | Runs the migrate command to Run all pending database migrations. |
| `app/Framework/Cli/Commands/MigrateRollbackCommand.php` | `MigrateRollbackCommand` | `Catalyst\Framework\Cli\Commands` | Runs the migrate:rollback command to Rollback the most recent migration batch. |
| `app/Framework/Cli/Commands/MigrateStatusCommand.php` | `MigrateStatusCommand` | `Catalyst\Framework\Cli\Commands` | Runs the migrate:status command to List discovered migrations and their execution status. |
| `app/Framework/Cli/Commands/ModuleLocalizationRegressionCommand.php` | `ModuleLocalizationRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the modules:localization-regression command to Verify manifest localization contract. |
| `app/Framework/Cli/Commands/OperationsRequestsRegressionCommand.php` | `OperationsRequestsRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the operations:requests-regression command to Verify Operations mutation payload boundaries. |
| `app/Framework/Cli/Commands/PluginListCommand.php` | `PluginListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the plugin:list command to List registered plugin manifests and runtime state. |
| `app/Framework/Cli/Commands/PluginToggleCommand.php` | `PluginToggleCommand` | `Catalyst\Framework\Cli\Commands` | Runs the plugin:toggle command to Enable or disable a plugin manifest at runtime. |
| `app/Framework/Cli/Commands/PresenceSmokeCommand.php` | `PresenceSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the presence:smoke command to Exercise canonical PA-08 claim-derived presence, heartbeat and reclaim semantics. |
| `app/Framework/Cli/Commands/QualityCheckCommand.php` | `QualityCheckCommand` | `Catalyst\Framework\Cli\Commands` | Runs the quality:check command to Run the standard local Composer, routing, structural, security and status checks. |
| `app/Framework/Cli/Commands/QueueFailedCommand.php` | `QueueFailedCommand` | `Catalyst\Framework\Cli\Commands` | Runs the queue:failed command to List failed jobs persisted by the framework queue. |
| `app/Framework/Cli/Commands/QueueRetryCommand.php` | `QueueRetryCommand` | `Catalyst\Framework\Cli\Commands` | Runs the queue:retry command to Retry one failed job or all failed jobs. |
| `app/Framework/Cli/Commands/QueueWorkCommand.php` | `QueueWorkCommand` | `Catalyst\Framework\Cli\Commands` | Runs the queue:work command to Process queued jobs from the framework queue backend. |
| `app/Framework/Cli/Commands/ReportingRunCommand.php` | `ReportingRunCommand` | `Catalyst\Framework\Cli\Commands` | Runs the reporting:run command to Queue a canonical PA-10 report run against the reusable reporting pipeline. |
| `app/Framework/Cli/Commands/ReportingSmokeCommand.php` | `ReportingSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the reporting:smoke command to Exercise canonical PA-10 queue + retry + persisted output flows over the unified reporting pipeline. |
| `app/Framework/Cli/Commands/RetentionRunCommand.php` | `RetentionRunCommand` | `Catalyst\Framework\Cli\Commands` | Runs the retention:run command to Inspect or execute canonical PA-05 retention / archive / purge policies. |
| `app/Framework/Cli/Commands/RetentionSmokeCommand.php` | `RetentionSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the retention:smoke command to Exercise canonical PA-05 dry-run, archive and purge flows over media, artifacts, attachments and audit rows. |
| `app/Framework/Cli/Commands/RolesMvcRegressionCommand.php` | `RolesMvcRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the roles:mvc-regression command to Verify Roles request and presentation boundaries. |
| `app/Framework/Cli/Commands/RouteBootstrapRegressionCommand.php` | `RouteBootstrapRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the route:bootstrap-regression command to Verify cache-safe middleware, module view paths and route discovery order. |
| `app/Framework/Cli/Commands/RouteCacheCommand.php` | `RouteCacheCommand` | `Catalyst\Framework\Cli\Commands` | Runs the route:cache command to Cache all registered routes to file. |
| `app/Framework/Cli/Commands/RouteClearCommand.php` | `RouteClearCommand` | `Catalyst\Framework\Cli\Commands` | Runs the route:clear command to Delete the route cache file. |
| `app/Framework/Cli/Commands/RouteLintCommand.php` | `RouteLintCommand` | `Catalyst\Framework\Cli\Commands` | Runs the route:lint command to Validate route casing, approved aliases and work/{slug} asset publication. |
| `app/Framework/Cli/Commands/RouteListCommand.php` | `RouteListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the route:list command to List registered routes with method, URI, name, handler and middleware. |
| `app/Framework/Cli/Commands/ScheduleListCommand.php` | `ScheduleListCommand` | `Catalyst\Framework\Cli\Commands` | Runs the schedule:list command to List registered framework schedule tasks. |
| `app/Framework/Cli/Commands/ScheduleRunCommand.php` | `ScheduleRunCommand` | `Catalyst\Framework\Cli\Commands` | Runs the schedule:run command to Evaluate the schedule registry and queue due tasks. |
| `app/Framework/Cli/Commands/SecurityCheckCommand.php` | `SecurityCheckCommand` | `Catalyst\Framework\Cli\Commands` | Runs the security:check command to Scan CSP/frontend hotspots and other low-hanging security regressions. |
| `app/Framework/Cli/Commands/SecurityRegressionCommand.php` | `SecurityRegressionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the security:regression command to Run focused regressions for inline JSON, reset/remember and signed local cache payloads. |
| `app/Framework/Cli/Commands/SensitivitySmokeCommand.php` | `SensitivitySmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the sensitivity:smoke command to Exercise canonical PA-03 classification and redaction across audit/API/export/version payloads. |
| `app/Framework/Cli/Commands/StatusCommand.php` | `StatusCommand` | `Catalyst\Framework\Cli\Commands` | Builds and renders system health sections for human or JSON CLI status checks. |
| `app/Framework/Cli/Commands/StorageCleanCommand.php` | `StorageCleanCommand` | `Catalyst\Framework\Cli\Commands` | Runs the storage:clean command to Remove route cache and runtime storage artifacts under boot-core/storage. |
| `app/Framework/Cli/Commands/TemporalSmokeCommand.php` | `TemporalSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the temporal:smoke command to Exercise canonical PA-04 temporal states and reusable validity SQL filters. |
| `app/Framework/Cli/Commands/TenancySmokeCommand.php` | `TenancySmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the tenancy:smoke command to Exercise canonical shared-db tenant boundaries with DB-backed read/write and audit checks. |
| `app/Framework/Cli/Commands/TenancyStatusCommand.php` | `TenancyStatusCommand` | `Catalyst\Framework\Cli\Commands` | Runs the tenancy:status command to Show the formal tenancy baseline and current resolver output. |
| `app/Framework/Cli/Commands/TimelineSmokeCommand.php` | `TimelineSmokeCommand` | `Catalyst\Framework\Cli\Commands` | Runs the timeline:smoke command to Exercise canonical PA-09 timeline start/stop/milestone semantics plus workflow-driven milestone capture. |
| `app/Framework/Cli/Commands/VersionCommand.php` | `VersionCommand` | `Catalyst\Framework\Cli\Commands` | Runs the version command to Display framework and PHP version. |
| `app/Framework/Cli/Support/PhpValueExporter.php` | `PhpValueExporter` | `Catalyst\Framework\Cli\Support` | Serializes scalar, list and associative values into deterministic PHP code fragments. |
| `app/Framework/Cli/Support/RouteContractInspector.php` | `RouteContractInspector` | `Catalyst\Framework\Cli\Support` | Collects route, middleware and asset-contract metadata for CLI inspection commands. |
| `app/Framework/Concurrency/RecordClaimManager.php` | `RecordClaimManager` | `Catalyst\Framework\Concurrency` | Acquires, renews, releases, validates, audits, and broadcasts record claim state. |
| `app/Framework/Concurrency/RecordClaimRepository.php` | `RecordClaimRepository` | `Catalyst\Framework\Concurrency` | Reads, locks, searches, and decorates record claims for concurrency workflows. |
| `app/Framework/Container/Container.php` | `Container` | `Catalyst\Framework\Container` | Registers bindings, shared instances, and resolves class dependencies through reflection. |
| `app/Framework/Controllers/CanonicalRedirectController.php` | `CanonicalRedirectController` | `Catalyst\Framework\Controllers` | Redirects alternate route aliases back to canonical framework URLs. |
| `app/Framework/Controllers/Controller.php` | `Controller` | `Catalyst\Framework\Controllers` | Provides shared response, view, validation, authorization, notification, and redirect helpers. |
| `app/Framework/Controllers/FlashController.php` | `FlashController` | `Catalyst\Framework\Controllers` | Handles client requests that dismiss flash messages from the active session. |
| `app/Framework/Database/Collection.php` | `Collection` | `Catalyst\Framework\Database` | Provides iterable, serializable transformation helpers for query results. |
| `app/Framework/Database/Connection.php` | `Connection` | `Catalyst\Framework\Database` | Wraps PDO connection lifecycle, queries and transaction callbacks. |
| `app/Framework/Database/DatabaseManager.php` | `DatabaseManager` | `Catalyst\Framework\Database` | Loads database configuration and lazily resolves named connections. |
| `app/Framework/Database/Migration.php` | `Migration` | `Catalyst\Framework\Database` | Provides migration versioning, connection access, SQL execution, table checks, and foreign key helpers. |
| `app/Framework/Database/MigrationRunner.php` | `MigrationRunner` | `Catalyst\Framework\Database` | Discovers migration files, tracks applied versions, executes batches, and maintains migration history. |
| `app/Framework/Database/Model.php` | `Model` | `Catalyst\Framework\Database` | Provides Active Record persistence, querying, casting and relationship entry points. |
| `app/Framework/Database/ModelQueryBuilder.php` | `ModelQueryBuilder` | `Catalyst\Framework\Database` | Hydrates query results into models and applies ORM scopes, eager loading and pagination. |
| `app/Framework/Database/Pagination.php` | `Pagination` | `Catalyst\Framework\Database` | Carries page items and pagination metadata for APIs and views. |
| `app/Framework/Database/PdoOptionsFactory.php` | `PdoOptionsFactory` | `Catalyst\Framework\Database` | Builds PDO option arrays without requiring unavailable driver constants. |
| `app/Framework/Database/QueryBuilder.php` | `QueryBuilder` | `Catalyst\Framework\Database` | Builds validated SQL clauses, bindings and aggregate statements for a table. |
| `app/Framework/Database/SqlReference.php` | `SqlReference` | `Catalyst\Framework\Database` | Guards table, column, alias, operator, and join fragments before they are interpolated into SQL. |
| `app/Framework/Database/Transaction.php` | `Transaction` | `Catalyst\Framework\Database` | Provides explicit begin, commit and rollback operations over one connection. |
| `app/Framework/Database/Concerns/HasModelAttributes.php` | `HasModelAttributes` | `Catalyst\Framework\Database\Concerns` | Manage in-memory ORM attributes, fill rules, type casts, dirty tracking, and array/JSON output. |
| `app/Framework/Database/Concerns/HasModelLifecycleHooks.php` | `HasModelLifecycleHooks` | `Catalyst\Framework\Database\Concerns` | Boot model traits once per concrete class and dispatch registered ORM lifecycle callbacks. |
| `app/Framework/Database/Concerns/HasModelRelationships.php` | `HasModelRelationships` | `Catalyst\Framework\Database\Concerns` | Build ORM relationship objects, cache loaded relations, and route property access to attributes or relations. |
| `app/Framework/Database/Concerns/PersistsModelState.php` | `PersistsModelState` | `Catalyst\Framework\Database\Concerns` | Persist ORM model state with lifecycle hooks, tenant filters, and optimistic locking safeguards. |
| `app/Framework/Database/Relations/BelongsTo.php` | `BelongsTo` | `Catalyst\Framework\Database\Relations` | Load and eager-match one owner model for each parent model by comparing parent foreign keys to related local keys. |
| `app/Framework/Database/Relations/BelongsToMany.php` | `BelongsToMany` | `Catalyst\Framework\Database\Relations` | Load pivot rows, query related models, and distribute related collections to parent model relation caches. |
| `app/Framework/Database/Relations/HasMany.php` | `HasMany` | `Catalyst\Framework\Database\Relations` | Load and eager-match collections of related models for each parent model key. |
| `app/Framework/Database/Relations/HasOne.php` | `HasOne` | `Catalyst\Framework\Database\Relations` | Load and eager-match one related model for each parent model key. |
| `app/Framework/Database/Relations/Relation.php` | `Relation` | `Catalyst\Framework\Database\Relations` | Store shared relation metadata and require lazy-load and eager-load implementations for concrete relations. |
| `app/Framework/Deployment/DeploymentManager.php` | `DeploymentManager` | `Catalyst\Framework\Deployment` | Builds release staging folders, optional ZIP artifacts, remote publishes, preflight summaries, and run records. |
| `app/Framework/Deployment/DeploymentRunRepository.php` | `DeploymentRunRepository` | `Catalyst\Framework\Deployment` | Searches, reads, creates, and updates deployment run persistence rows. |
| `app/Framework/Document/DocumentTemplateManager.php` | `DocumentTemplateManager` | `Catalyst\Framework\Document` | Creates, previews, exports, archives, purges, and transitions tenant document templates and artifacts. |
| `app/Framework/Document/DocumentTemplateRepository.php` | `DocumentTemplateRepository` | `Catalyst\Framework\Document` | Persists, reads, searches, archives, and purges document template and artifact records. |
| `app/Framework/Document/TemplateStringRenderer.php` | `TemplateStringRenderer` | `Catalyst\Framework\Document` | Resolves dotted payload paths, conditional blocks, and scalar replacements inside template strings. |
| `app/Framework/Document/Pdf/PdfRendererInterface.php` | `PdfRendererInterface` | `Catalyst\Framework\Document\Pdf` | Defines the boundary for rendering document title and HTML/text content into PDF bytes. |
| `app/Framework/Document/Pdf/SimplePdfWriter.php` | `SimplePdfWriter` | `Catalyst\Framework\Document\Pdf` | Builds a basic PDF document stream from title and text content without external rendering dependencies. |
| `app/Framework/Documentation/RuntimeInventoryGenerator.php` | `RuntimeInventoryGenerator` | `Catalyst\Framework\Documentation` | Scans project files and emits Markdown inventory sections for runtime documentation. |
| `app/Framework/Enums/AppEnvironment.php` | `AppEnvironment` | `Catalyst\Framework\Enums` | Normalizes environment names and exposes environment capability checks. |
| `app/Framework/Event/EventBus.php` | `EventBus` | `Catalyst\Framework\Event` | Registers listeners, dispatches event envelopes, queues eligible listeners, and invokes synchronous handlers. |
| `app/Framework/Event/EventListenerDefinition.php` | `EventListenerDefinition` | `Catalyst\Framework\Event` | Carries listener target, queue eligibility, and queue name for event dispatch. |
| `app/Framework/Event/EventListenerInterface.php` | `EventListenerInterface` | `Catalyst\Framework\Event` | Defines the handler method required for class-based event listeners. |
| `app/Framework/Event/FrameworkEventCatalog.php` | `FrameworkEventCatalog` | `Catalyst\Framework\Event` | Registers default audit, timeline, notification, and automation event listeners on the event bus. |
| `app/Framework/Event/Listeners/CaptureAuditEventListener.php` | `CaptureAuditEventListener` | `Catalyst\Framework\Event\Listeners` | Writes audit log entries from structured event envelope payloads. |
| `app/Framework/Event/Listeners/CaptureTimelineMilestoneListener.php` | `CaptureTimelineMilestoneListener` | `Catalyst\Framework\Event\Listeners` | Records configured timeline events from event envelope payloads. |
| `app/Framework/Event/Listeners/DeliverNotificationListener.php` | `DeliverNotificationListener` | `Catalyst\Framework\Event\Listeners` | Converts notification event payloads into stored or broadcast user notifications. |
| `app/Framework/Event/Listeners/ProcessAutomationEventListener.php` | `ProcessAutomationEventListener` | `Catalyst\Framework\Event\Listeners` | Invokes automation rule execution when framework events are dispatched. |
| `app/Framework/FeatureFlag/FeatureFlagManager.php` | `FeatureFlagManager` | `Catalyst\Framework\FeatureFlag` | Merges configured, plugin and system-owned flags, persists editable definitions and refreshes route discovery after changes. |
| `app/Framework/FeatureFlag/FeatureFlagOverrideRepository.php` | `FeatureFlagOverrideRepository` | `Catalyst\Framework\FeatureFlag` | Queries override records, applies actor precedence and records repository audit events for override mutations. |
| `app/Framework/Geo/BoundingBox.php` | `BoundingBox` | `Catalyst\Framework\Geo` | Stores box edges, validates north-south bounds and tests whether coordinates fall inside the box. |
| `app/Framework/Geo/Coordinate.php` | `Coordinate` | `Catalyst\Framework\Geo` | Validates latitude, normalizes longitude and exposes degree/radian values for distance calculations. |
| `app/Framework/Geo/GeoManager.php` | `GeoManager` | `Catalyst\Framework\Geo` | Creates validated coordinates, calculates great-circle distances and derives radius-based bounding boxes. |
| `app/Framework/Health/HealthReportBuilder.php` | `HealthReportBuilder` | `Catalyst\Framework\Health` | Aggregates runtime, configuration, cache, queue, scheduler, storage, secret and route contract checks into a status payload. |
| `app/Framework/Http/ApiRequest.php` | `ApiRequest` | `Catalyst\Framework\Http` | Normalizes request data from JSON, form-url-encoded and multipart bodies and identifies API/AJAX request intent. |
| `app/Framework/Http/ErrorResponseFactory.php` | `ErrorResponseFactory` | `Catalyst\Framework\Http` | Renders localized error surfaces and falls back to escaped inline HTML when the view layer is unavailable. |
| `app/Framework/Http/FileValidator.php` | `FileValidator` | `Catalyst\Framework\Http` | Confirms upload validity and enforces allowed MIME, extension and maximum-size rules for form validation. |
| `app/Framework/Http/FormRequest.php` | `FormRequest` | `Catalyst\Framework\Http` | Filters request payloads, runs validation rules, exposes validated data and prepares safe old input for failed submissions. |
| `app/Framework/Http/HtmlResponse.php` | `HtmlResponse` | `Catalyst\Framework\Http` | Ensures HTML responses carry the correct Content-Type header before delegating response transport to the base class. |
| `app/Framework/Http/JsonResponse.php` | `JsonResponse` | `Catalyst\Framework\Http` | Encodes payloads, attaches notification metadata and exposes API response helpers for JSON clients. |
| `app/Framework/Http/RedirectResponse.php` | `RedirectResponse` | `Catalyst\Framework\Http` | Validates redirect status codes, sets Location headers and provides fallback HTML redirect content. |
| `app/Framework/Http/RedirectTarget.php` | `RedirectTarget` | `Catalyst\Framework\Http` | Rejects unsafe redirect destinations and builds login redirect URLs for protected surfaces. |
| `app/Framework/Http/Request.php` | `Request` | `Catalyst\Framework\Http` | Stores sanitized superglobal data, parses JSON input, resolves headers, files, attributes and request metadata for framework consumers. |
| `app/Framework/Http/Response.php` | `Response` | `Catalyst\Framework\Http` | Stores response content, status, headers and internal attributes, then sends headers and body to the client. |
| `app/Framework/Http/UploadedFile.php` | `UploadedFile` | `Catalyst\Framework\Http` | Exposes upload metadata, validates transfer state, detects MIME type and moves or stores uploaded files. |
| `app/Framework/Idempotency/IdempotencyConflictException.php` | `IdempotencyConflictException` | `Catalyst\Framework\Idempotency` | Represents requests whose idempotency key is missing required scope data or is bound to a different fingerprint. |
| `app/Framework/Idempotency/IdempotencyInProgressException.php` | `IdempotencyInProgressException` | `Catalyst\Framework\Idempotency` | Represents replay attempts made before the original idempotent operation has completed. |
| `app/Framework/Idempotency/IdempotencyManager.php` | `IdempotencyManager` | `Catalyst\Framework\Idempotency` | Generates keys, fingerprints requests, records operation outcomes and replays completed results for matching keys. |
| `app/Framework/Idempotency/IdempotencyRepository.php` | `IdempotencyRepository` | `Catalyst\Framework\Idempotency` | Finds, creates and completes idempotency keys for the current tenant. |
| `app/Framework/Localization/LocalizationManager.php` | `LocalizationManager` | `Catalyst\Framework\Localization` | Resolves supported locales, writes runtime language configuration and reports, initializes or synchronizes translation catalogs. |
| `app/Framework/Mail/DkimGenerator.php` | `DkimGenerator` | `Catalyst\Framework\Mail` | Generate DKIM key material and DNS records for mail signing. |
| `app/Framework/Mail/MailAttachment.php` | `MailAttachment` | `Catalyst\Framework\Mail` | Preserve typed attachment metadata for compatibility callers. |
| `app/Framework/Mail/MailManager.php` | `MailManager` | `Catalyst\Framework\Mail` | Configure PHPMailer and deliver framework mail messages. |
| `app/Framework/Mail/MailMessage.php` | `MailMessage` | `Catalyst\Framework\Mail` | Hold and validate per-message mail state for PHPMailer delivery. |
| `app/Framework/Mail/MailTemplate.php` | `MailTemplate` | `Catalyst\Framework\Mail` | Render mail templates from the configured email template root. |
| `app/Framework/Media/MediaManager.php` | `MediaManager` | `Catalyst\Framework\Media` | Store, replace, archive and delete media items with metadata sync. |
| `app/Framework/Media/MediaRepository.php` | `MediaRepository` | `Catalyst\Framework\Media` | Read tenant-scoped media rows with metadata-aware filters and lookups. |
| `app/Framework/Metadata/MetadataFieldRepository.php` | `MetadataFieldRepository` | `Catalyst\Framework\Metadata` | Read, persist and delete tenant-scoped metadata field definitions. |
| `app/Framework/Metadata/MetadataManager.php` | `MetadataManager` | `Catalyst\Framework\Metadata` | Build metadata form, grid, validation and definition payload contracts. |
| `app/Framework/Metadata/MetadataResourceRegistry.php` | `MetadataResourceRegistry` | `Catalyst\Framework\Metadata` | Register and resolve metadata-enabled resource definitions. |
| `app/Framework/Metadata/MetadataValueRepository.php` | `MetadataValueRepository` | `Catalyst\Framework\Metadata` | Resolve, display and sync tenant-scoped metadata values. |
| `app/Framework/Middleware/ApiTokenMiddleware.php` | `ApiTokenMiddleware` | `Catalyst\Framework\Middleware` | Extracts bearer tokens, resolves active API token users, and scopes authentication for API requests. |
| `app/Framework/Middleware/AuthMiddleware.php` | `AuthMiddleware` | `Catalyst\Framework\Middleware` | Allows authenticated requests, restores remember-me sessions, or rejects unauthenticated access. |
| `app/Framework/Middleware/BasicAuthMiddleware.php` | `BasicAuthMiddleware` | `Catalyst\Framework\Middleware` | Validates configured Basic Auth credentials and throttles repeated failed attempts by client IP. |
| `app/Framework/Middleware/CallableMiddleware.php` | `CallableMiddleware` | `Catalyst\Framework\Middleware` | Adapts a callable so it can participate in the middleware pipeline. |
| `app/Framework/Middleware/CanonicalPathRedirectMiddleware.php` | `CanonicalPathRedirectMiddleware` | `Catalyst\Framework\Middleware` | Resolves canonical targets and emits permanent redirects before route execution. |
| `app/Framework/Middleware/CoreMiddleware.php` | `CoreMiddleware` | `Catalyst\Framework\Middleware` | Provides shared pipeline forwarding, logging, and request inspection helpers. |
| `app/Framework/Middleware/CorsMiddleware.php` | `CorsMiddleware` | `Catalyst\Framework\Middleware` | Loads CORS policy, handles preflight requests, and appends cross-origin response headers. |
| `app/Framework/Middleware/CsrfMiddleware.php` | `CsrfMiddleware` | `Catalyst\Framework\Middleware` | Validates CSRF tokens for state-changing browser requests while honoring explicit exemptions. |
| `app/Framework/Middleware/DebugMiddleware.php` | `DebugMiddleware` | `Catalyst\Framework\Middleware` | Logs request and response metadata around downstream middleware execution. |
| `app/Framework/Middleware/DevToolsGuardMiddleware.php` | `DevToolsGuardMiddleware` | `Catalyst\Framework\Middleware` | Restricts developer tooling routes to authorized users in development environments. |
| `app/Framework/Middleware/FeatureFlagInterface.php` | `FeatureFlagInterface` | `Catalyst\Framework\Middleware` | Defines the runtime enablement contract for configurable middleware. |
| `app/Framework/Middleware/GuestMiddleware.php` | `GuestMiddleware` | `Catalyst\Framework\Middleware` | Redirects authenticated users away from routes reserved for guests. |
| `app/Framework/Middleware/LoginThrottleMiddleware.php` | `LoginThrottleMiddleware` | `Catalyst\Framework\Middleware` | Limits repeated login and registration attempts per client IP outside development. |
| `app/Framework/Middleware/MiddlewareInterface.php` | `MiddlewareInterface` | `Catalyst\Framework\Middleware` | Defines how middleware processes a request and delegates to the next handler. |
| `app/Framework/Middleware/MiddlewareStack.php` | `MiddlewareStack` | `Catalyst\Framework\Middleware` | Stores middleware definitions, resolves them, and executes the resulting request chain. |
| `app/Framework/Middleware/RequestThrottlingMiddleware.php` | `RequestThrottlingMiddleware` | `Catalyst\Framework\Middleware` | Resolves a request throttle policy, updates its bucket, and rejects requests during lockout. |
| `app/Framework/Middleware/RoleMiddleware.php` | `RoleMiddleware` | `Catalyst\Framework\Middleware` | Requires authentication and enforces configured RBAC role and permission requirements. |
| `app/Framework/Middleware/RouteFeatureMiddleware.php` | `RouteFeatureMiddleware` | `Catalyst\Framework\Middleware` | Allows enabled feature routes and returns the configured unavailable response otherwise. |
| `app/Framework/Middleware/SecurityHeadersMiddleware.php` | `SecurityHeadersMiddleware` | `Catalyst\Framework\Middleware` | Adds security headers and content security policy appropriate to the response profile. |
| `app/Framework/Middleware/SetupAccessTrait.php` | `SetupAccessTrait` | `Catalyst\Framework\Middleware` | Normalizes setup paths, recognizes bypass routes, and builds setup JSON errors. |
| `app/Framework/Middleware/SetupGuardMiddleware.php` | `SetupGuardMiddleware` | `Catalyst\Framework\Middleware` | Keeps first-run setup reachable while requiring an authenticated admin after configuration. |
| `app/Framework/Middleware/SetupMiddleware.php` | `SetupMiddleware` | `Catalyst\Framework\Middleware` | Redirects unconfigured application requests to setup while preserving required bypass routes. |
| `app/Framework/Middleware/TenancyContextMiddleware.php` | `TenancyContextMiddleware` | `Catalyst\Framework\Middleware` | Resolves the request tenant and mirrors its context into the initialized session. |
| `app/Framework/Middleware/ThrottleProfileCatalog.php` | `ThrottleProfileCatalog` | `Catalyst\Framework\Middleware` | Resolves route-specific throttle configuration or derives a default profile from the request path. |
| `app/Framework/Middleware/WebSocketBootMiddleware.php` | `WebSocketBootMiddleware` | `Catalyst\Framework\Middleware` | Performs throttled WebSocket liveness checks and launches the local server when required. |
| `app/Framework/Module/BuiltInModuleDeclarations.php` | `BuiltInModuleDeclarations` | `Catalyst\Framework\Module` | Provides the static framework module declaration catalog to runtime registries. |
| `app/Framework/Module/ModuleAssetPublisher.php` | `ModuleAssetPublisher` | `Catalyst\Framework\Module` | Copies scaffolded style and script assets into their public work paths. |
| `app/Framework/Module/ModuleBlueprintFactory.php` | `ModuleBlueprintFactory` | `Catalyst\Framework\Module` | Validates scaffold input and assembles manifest, paths, and generated file definitions. |
| `app/Framework/Module/ModuleDiscovery.php` | `ModuleDiscovery` | `Catalyst\Framework\Module` | Builds the baseline runtime metadata for modules found on disk. |
| `app/Framework/Module/ModuleFileFactory.php` | `ModuleFileFactory` | `Catalyst\Framework\Module` | Renders controller, view, asset, localization, route, and manifest file contents. |
| `app/Framework/Module/ModuleHarnessInspector.php` | `ModuleHarnessInspector` | `Catalyst\Framework\Module` | Classifies module surfaces, routes, assets, and expected access outcomes for harness checks. |
| `app/Framework/Module/ModuleInspector.php` | `ModuleInspector` | `Catalyst\Framework\Module` | Produces module reports combining discovery, routing, permissions, navigation, and asset state. |
| `app/Framework/Module/ModuleLinter.php` | `ModuleLinter` | `Catalyst\Framework\Module` | Detects manifest, routing, asset, permission, navigation, and plugin inconsistencies. |
| `app/Framework/Module/ModuleLocalizationDecorator.php` | `ModuleLocalizationDecorator` | `Catalyst\Framework\Module` | Replaces translatable module metadata keys and applies DevTools-specific translations. |
| `app/Framework/Module/ModuleManifestBuilder.php` | `ModuleManifestBuilder` | `Catalyst\Framework\Module` | Produces manifest declarations for routes, permissions, guards, and navigation. |
| `app/Framework/Module/ModuleManifestLoader.php` | `ModuleManifestLoader` | `Catalyst\Framework\Module` | Requires module manifests safely, reports validation errors, and registers localization paths. |
| `app/Framework/Module/ModuleRegistry.php` | `ModuleRegistry` | `Catalyst\Framework\Module` | Combines discovery, declarations, manifests, localization, runtime state, and route ownership. |
| `app/Framework/Module/ModuleRouteOwnershipResolver.php` | `ModuleRouteOwnershipResolver` | `Catalyst\Framework\Module` | Maps route handlers back to modules and annotates module metadata with owned route patterns. |
| `app/Framework/Module/ModuleRuntimeDocsGenerator.php` | `ModuleRuntimeDocsGenerator` | `Catalyst\Framework\Module` | Renders inspection, harness, and lint results into a Markdown module inventory. |
| `app/Framework/Module/ModuleRuntimeStateDecorator.php` | `ModuleRuntimeStateDecorator` | `Catalyst\Framework\Module` | Combines plugin and feature-flag state into module enablement metadata. |
| `app/Framework/Module/ModuleScaffoldService.php` | `ModuleScaffoldService` | `Catalyst\Framework\Module` | Previews blueprints, writes generated module files, and publishes generated assets. |
| `app/Framework/Navigation/NavigationRegistry.php` | `NavigationRegistry` | `Catalyst\Framework\Navigation` | Resolves administrative shells, public menus, breadcrumbs, and visibility rules. |
| `app/Framework/Notification/Notification.php` | `Notification` | `Catalyst\Framework\Notification` | Carries immutable notification content and creates standard notification variants. |
| `app/Framework/Notification/NotificationBag.php` | `NotificationBag` | `Catalyst\Framework\Notification` | Collects toaster, modal, and inline alert payloads for JSON responses. |
| `app/Framework/Notification/NotificationManager.php` | `NotificationManager` | `Catalyst\Framework\Notification` | Persists notification dispatches, emits events, queues delivery, and publishes WebSocket updates. |
| `app/Framework/Notification/NotificationPosition.php` | `NotificationPosition` | `Catalyst\Framework\Notification` | Maps toaster positions to CSS placement and stacking direction. |
| `app/Framework/Notification/NotificationRepository.php` | `NotificationRepository` | `Catalyst\Framework\Notification` | Persists user notifications and updates their read state without physical deletion. |
| `app/Framework/Notification/NotificationType.php` | `NotificationType` | `Catalyst\Framework\Notification` | Maps notification semantic types to Bootstrap classes, icons, and contrast styles. |
| `app/Framework/Plugin/PluginManager.php` | `PluginManager` | `Catalyst\Framework\Plugin` | Reads plugin configuration, toggles optional plugins, audits changes, and refreshes discovery caches. |
| `app/Framework/Plugin/PluginRegistry.php` | `PluginRegistry` | `Catalyst\Framework\Plugin` | Discovers plugin manifests, records validation errors, and resolves module ownership. |
| `app/Framework/Presence/PresenceManager.php` | `PresenceManager` | `Catalyst\Framework\Presence` | Converts record claims into presence payloads and broadcasts claim snapshots through WebSocket. |
| `app/Framework/Queue/QueueJobSerializer.php` | `QueueJobSerializer` | `Catalyst\Framework\Queue` | Converts queueable jobs between runtime objects and repository payloads while enforcing their contract. |
| `app/Framework/Queue/QueueManager.php` | `QueueManager` | `Catalyst\Framework\Queue` | Resolves queue routing, persists new jobs, and emits dispatch events. |
| `app/Framework/Queue/QueueRepository.php` | `QueueRepository` | `Catalyst\Framework\Queue` | Provides the database operations required to enqueue, reserve, retry, complete, inspect, and prune queued work. |
| `app/Framework/Queue/QueueSchemaManager.php` | `QueueSchemaManager` | `Catalyst\Framework\Queue` | Creates pending and failed queue tables once per request using the configured database connection. |
| `app/Framework/Queue/QueueSettings.php` | `QueueSettings` | `Catalyst\Framework\Queue` | Provides validated queue connection, table, default queue, and stale-reservation settings. |
| `app/Framework/Queue/QueueWorker.php` | `QueueWorker` | `Catalyst\Framework\Queue` | Reserves jobs, executes them, persists their outcome, and emits queue lifecycle events. |
| `app/Framework/Queue/QueueableJobInterface.php` | `QueueableJobInterface` | `Catalyst\Framework\Queue` | Standardizes execution, queue routing, retry policy, and payload serialization for queued work. |
| `app/Framework/Queue/Jobs/DispatchNotificationJob.php` | `DispatchNotificationJob` | `Catalyst\Framework\Queue\Jobs` | Carries notification state across the queue boundary and executes notification delivery. |
| `app/Framework/Queue/Jobs/InvokeQueuedListenerJob.php` | `InvokeQueuedListenerJob` | `Catalyst\Framework\Queue\Jobs` | Carries an event and listener identity across the queue boundary and dispatches the restored listener. |
| `app/Framework/Queue/Jobs/PruneQueueHistoryJob.php` | `PruneQueueHistoryJob` | `Catalyst\Framework\Queue\Jobs` | Executes periodic cleanup for queue failures and scheduler run records. |
| `app/Framework/Reporting/ReportingManager.php` | `ReportingManager` | `Catalyst\Framework\Reporting` | Persists report runs, builds report rows, stores generated exports, and optionally attaches outputs to resources. |
| `app/Framework/Reporting/Jobs/RunReportJob.php` | `RunReportJob` | `Catalyst\Framework\Reporting\Jobs` | Carries a report-run identifier across the queue boundary and invokes report generation. |
| `app/Framework/Retention/RetentionManager.php` | `RetentionManager` | `Catalyst\Framework\Retention` | Selects eligible tenant records, applies retention actions, and writes operational audit entries. |
| `app/Framework/Retention/Jobs/RunRetentionPoliciesJob.php` | `RunRetentionPoliciesJob` | `Catalyst\Framework\Retention\Jobs` | Carries retention scope across the queue boundary and invokes policy evaluation. |
| `app/Framework/Route/CanonicalPathRedirector.php` | `CanonicalPathRedirector` | `Catalyst\Framework\Route` | Normalizes incoming paths and returns redirects only when a legacy route prefix maps to a different canonical path. |
| `app/Framework/Route/GlobalMiddlewareRegistrar.php` | `GlobalMiddlewareRegistrar` | `Catalyst\Framework\Route` | Defines the global middleware order and adds that stack to the router. |
| `app/Framework/Route/Route.php` | `Route` | `Catalyst\Framework\Route` | Stores one route definition, matches URIs, restores cached middleware, and generates route URLs. |
| `app/Framework/Route/RouteCollection.php` | `RouteCollection` | `Catalyst\Framework\Route` | Indexes routes by method and name, performs route matching, and exposes reverse URL generation. |
| `app/Framework/Route/RouteCompiler.php` | `RouteCompiler` | `Catalyst\Framework\Route` | Compiles route templates, optional segments, and parameter constraints into URI-matching regular expressions. |
| `app/Framework/Route/RouteDispatcher.php` | `RouteDispatcher` | `Catalyst\Framework\Route` | Matches requests, runs middleware, resolves handler dependencies, executes handlers, and normalizes responses. |
| `app/Framework/Route/RouteGroup.php` | `RouteGroup` | `Catalyst\Framework\Route` | Merges nested route-group metadata and exposes normalized group attributes. |
| `app/Framework/Route/Router.php` | `Router` | `Catalyst\Framework\Route` | Registers routes and middleware, manages route caching, and dispatches HTTP requests. |
| `app/Framework/Route/UrlGenerator.php` | `UrlGenerator` | `Catalyst\Framework\Route` | Generates relative and absolute URLs from named routes, arbitrary paths, and asset paths. |
| `app/Framework/Schedule/CronExpression.php` | `CronExpression` | `Catalyst\Framework\Schedule` | Determines whether a scheduled task is due by matching cron segments, ranges, lists, and steps. |
| `app/Framework/Schedule/FrameworkScheduleCatalog.php` | `FrameworkScheduleCatalog` | `Catalyst\Framework\Schedule` | Adds queue-history cleanup, automation evaluation, and retention execution to the scheduler registry once. |
| `app/Framework/Schedule/ScheduleLockManager.php` | `ScheduleLockManager` | `Catalyst\Framework\Schedule` | Creates task-specific lock files and executes callbacks only while holding an exclusive non-blocking lock. |
| `app/Framework/Schedule/ScheduleRegistry.php` | `ScheduleRegistry` | `Catalyst\Framework\Schedule` | Loads framework defaults and indexes scheduled tasks by their unique name. |
| `app/Framework/Schedule/ScheduleRepository.php` | `ScheduleRepository` | `Catalyst\Framework\Schedule` | Prevents duplicate slot dispatches, records queued or skipped runs, summarizes history, and prunes old records. |
| `app/Framework/Schedule/ScheduleRunner.php` | `ScheduleRunner` | `Catalyst\Framework\Schedule` | Resolves task scope, checks cron slots, prevents duplicate execution, queues due jobs, and reports outcomes. |
| `app/Framework/Schedule/ScheduleSchemaManager.php` | `ScheduleSchemaManager` | `Catalyst\Framework\Schedule` | Creates the scheduler history table once per request using the queue database connection. |
| `app/Framework/Schedule/ScheduleSettings.php` | `ScheduleSettings` | `Catalyst\Framework\Schedule` | Provides the scheduler enabled flag and history-table setting. |
| `app/Framework/Security/SignedSerializedPayload.php` | `SignedSerializedPayload` | `Catalyst\Framework\Security` | Protects cached serialized values with an HMAC signature and an explicit allowed-class list. |
| `app/Framework/Sensitivity/DataClassificationRegistry.php` | `DataClassificationRegistry` | `Catalyst\Framework\Sensitivity` | Resolves the sanitization policy for a resource field and output channel. |
| `app/Framework/Sensitivity/SensitiveDataPolicy.php` | `SensitiveDataPolicy` | `Catalyst\Framework\Sensitivity` | Sanitizes structured data before it leaves its intended disclosure boundary. |
| `app/Framework/Session/DatabaseSessionHandler.php` | `DatabaseSessionHandler` | `Catalyst\Framework\Session` | Implements database-backed session reads, writes, cleanup and table bootstrap. |
| `app/Framework/Session/FlashBag.php` | `FlashBag` | `Catalyst\Framework\Session` | Persists, consumes and deduplicates flash-message state in the session. |
| `app/Framework/Session/FlashMessage.php` | `FlashMessage` | `Catalyst\Framework\Session` | Exposes the controller-facing API for one-shot and persistent flash messages. |
| `app/Framework/Session/SessionManager.php` | `SessionManager` | `Catalyst\Framework\Session` | Initializes PHP sessions and provides storage, migration and form-state helpers. |
| `app/Framework/Session/ToastQueue.php` | `ToastQueue` | `Catalyst\Framework\Session` | Buffers one-shot toast notifications and drains them on the next read. |
| `app/Framework/Storage/FtpStorageAdapter.php` | `FtpStorageAdapter` | `Catalyst\Framework\Storage` | Implements remote object storage operations from validated transfer configuration. |
| `app/Framework/Storage/LocalStorageAdapter.php` | `LocalStorageAdapter` | `Catalyst\Framework\Storage` | Provides normalized local file persistence and optional public URLs. |
| `app/Framework/Storage/StorageAdapterInterface.php` | `StorageAdapterInterface` | `Catalyst\Framework\Storage` | Standardizes object storage, retrieval, deletion and URL resolution. |
| `app/Framework/Storage/StorageManager.php` | `StorageManager` | `Catalyst\Framework\Storage` | Provides a stable facade over local, runtime and remote storage adapters. |
| `app/Framework/Temporal/EffectiveWindow.php` | `EffectiveWindow` | `Catalyst\Framework\Temporal` | Normalizes date values and derives active, scheduled or expired state. |
| `app/Framework/Tenancy/TenancyManager.php` | `TenancyManager` | `Catalyst\Framework\Tenancy` | Exposes tenant configuration, host resolution and request-scoped context overrides. |
| `app/Framework/Testing/AuthFixtureCatalog.php` | `AuthFixtureCatalog` | `Catalyst\Framework\Testing` | Supplies fixture users, roles and permissions by profile key. |
| `app/Framework/Testing/AuthFixtureFactory.php` | `AuthFixtureFactory` | `Catalyst\Framework\Testing` | Converts fixture definitions into persistence-ready user and MFA payloads. |
| `app/Framework/Testing/AuthFixtureManager.php` | `AuthFixtureManager` | `Catalyst\Framework\Testing` | Manages reversible tenant-scoped auth fixture profiles for development and QA. |
| `app/Framework/Timeline/TimelineManager.php` | `TimelineManager` | `Catalyst\Framework\Timeline` | Orchestrates lifecycle events, elapsed-time summaries and timeline notifications. |
| `app/Framework/Timeline/TimelineRepository.php` | `TimelineRepository` | `Catalyst\Framework\Timeline` | Provides ordered timeline history and normalized event rows for resources. |
| `app/Framework/Traits/BelongsToTenantTrait.php` | `BelongsToTenantTrait` | `Catalyst\Framework\Traits` | Stamps missing tenant identifiers and rejects cross-tenant inserts. |
| `app/Framework/Traits/ErrorTypeTrait.php` | `ErrorTypeTrait` | `Catalyst\Framework\Traits` | Maps PHP error-level constants to readable labels. |
| `app/Framework/Traits/FrontResourceTrait.php` | `FrontResourceTrait` | `Catalyst\Framework\Traits` | Publishes module-scoped frontend assets and exposes their module slug to views. |
| `app/Framework/Traits/HandlesFormEventsTrait.php` | `HandlesFormEventsTrait` | `Catalyst\Framework\Traits` | Routes submitted form event names to controller handler methods. |
| `app/Framework/Traits/HasAuditLogTrait.php` | `HasAuditLogTrait` | `Catalyst\Framework\Traits` | Stamps actor identifiers and records model lifecycle mutations. |
| `app/Framework/Traits/HasOptimisticLockingTrait.php` | `HasOptimisticLockingTrait` | `Catalyst\Framework\Traits` | Rejects stale writes and increments optimistic lock versions. |
| `app/Framework/Traits/HasSoftDeletesTrait.php` | `HasSoftDeletesTrait` | `Catalyst\Framework\Traits` | Replaces destructive model deletion with restorable timestamp markers. |
| `app/Framework/Traits/HasTimestampsTrait.php` | `HasTimestampsTrait` | `Catalyst\Framework\Traits` | Maintains creation and update timestamps through model lifecycle hooks. |
| `app/Framework/Traits/InteractsWithRecordClaimsTrait.php` | `InteractsWithRecordClaimsTrait` | `Catalyst\Framework\Traits` | Acquires, validates, releases and exposes concurrency claim state. |
| `app/Framework/Traits/LoadsFeatureConfigTrait.php` | `LoadsFeatureConfigTrait` | `Catalyst\Framework\Traits` | Loads feature configuration once per instance with resilient defaults. |
| `app/Framework/Traits/OutputCleanerTrait.php` | `OutputCleanerTrait` | `Catalyst\Framework\Traits` | Resets output buffering before framework error rendering. |
| `app/Framework/Traits/SingletonTrait.php` | `SingletonTrait` | `Catalyst\Framework\Traits` | Provides controlled singleton instantiation, replacement and reset behavior. |
| `app/Framework/Versioning/VersionManager.php` | `VersionManager` | `Catalyst\Framework\Versioning` | Computes snapshot differences and delegates restoration to resource owners. |
| `app/Framework/Versioning/VersionRepository.php` | `VersionRepository` | `Catalyst\Framework\Versioning` | Provides ordered version lookups and normalized snapshots for versioned resources. |
| `app/Framework/View/HtmlAllowlistSanitizer.php` | `HtmlAllowlistSanitizer` | `Catalyst\Framework\View` | Removes unsafe markup, attributes and URLs before trusted rendering. |
| `app/Framework/View/InlineJson.php` | `InlineJson` | `Catalyst\Framework\View` | Applies browser-safe JSON flags and returns a stable fallback on encoding failure. |
| `app/Framework/View/ModuleViewPathRegistrar.php` | `ModuleViewPathRegistrar` | `Catalyst\Framework\View` | Adds valid module view namespaces without exposing missing directories. |
| `app/Framework/View/TrustedHtml.php` | `TrustedHtml` | `Catalyst\Framework\View` | Marks trusted HTML fragments so renderers can distinguish them from escaped values. |
| `app/Framework/View/View.php` | `View` | `Catalyst\Framework\View` | Resolves templates, prepares rendering scope and delegates constrained token rendering. |
| `app/Framework/View/ViewTokenRenderer.php` | `ViewTokenRenderer` | `Catalyst\Framework\View` | Evaluates the constrained token-template language without executing PHP. |
| `app/Framework/WebSocket/WebSocketPublisher.php` | `WebSocketPublisher` | `Catalyst\Framework\WebSocket` | Sends notification and resource payloads to the internal WebSocket publisher endpoint. |
| `app/Framework/WebSocket/WebSocketServer.php` | `WebSocketServer` | `Catalyst\Framework\WebSocket` | Authenticates connections, manages subscriptions and broadcasts user or resource payloads. |
| `app/Framework/WebSocket/WebSocketToken.php` | `WebSocketToken` | `Catalyst\Framework\WebSocket` | Issues and verifies stateless WebSocket authentication tokens with tenant context. |
| `app/Framework/Workflow/FrameworkWorkflowCatalog.php` | `FrameworkWorkflowCatalog` | `Catalyst\Framework\Workflow` | Supplies lifecycle definitions and built-in transition side effects. |
| `app/Framework/Workflow/WorkflowDefinition.php` | `WorkflowDefinition` | `Catalyst\Framework\Workflow` | Provides immutable workflow structure and transition lookups. |
| `app/Framework/Workflow/WorkflowDefinitionRegistry.php` | `WorkflowDefinitionRegistry` | `Catalyst\Framework\Workflow` | Registers built-in definitions and resolves definitions for workflow execution. |
| `app/Framework/Workflow/WorkflowManager.php` | `WorkflowManager` | `Catalyst\Framework\Workflow` | Enforces transition permissions and guards while persisting and dispatching lifecycle events. |
| `app/Framework/Workflow/WorkflowRepository.php` | `WorkflowRepository` | `Catalyst\Framework\Workflow` | Provides workflow state lookups, mutations and transition audit queries. |
| `app/Helpers/Config/AppEntryCatalog.php` | `AppEntryCatalog` | `Catalyst\Helpers\Config` | Supplies selectable entry labels, keys and route paths for configured surfaces. |
| `app/Helpers/Config/ConfigManager.php` | `ConfigManager` | `Catalyst\Helpers\Config` | Loads, exposes and persists environment configuration while isolating secret values. |
| `app/Helpers/Config/ConfigSecretCatalog.php` | `ConfigSecretCatalog` | `Catalyst\Helpers\Config` | Splits, merges and audits secret values for managed configuration sections. |
| `app/Helpers/Config/ConfigSecretStore.php` | `ConfigSecretStore` | `Catalyst\Helpers\Config` | Loads, writes, merges and audits secrets for one runtime environment. |
| `app/Helpers/Debug/ColorType.php` | `ColorType` | `Catalyst\Helpers\Debug;` | Enumerates the semantic color slots supported by dumper palettes. |
| `app/Helpers/Debug/Dumper.php` | `Dumper` | `Catalyst\Helpers\Debug;` | Coordinates dumper configuration, formatting, and rendering for each debug inspection. |
| `app/Helpers/Debug/DumperCollapsible.php` | `DumperCollapsible` | `Catalyst\Helpers\Debug;` | Builds collapsible debug sections and their CSP-safe browser behavior. |
| `app/Helpers/Debug/DumperColorizer.php` | `DumperColorizer` | `Catalyst\Helpers\Debug;` | Resolves theme colors and applies them to dumper output for HTML or CLI rendering. |
| `app/Helpers/Debug/DumperConfig.php` | `DumperConfig` | `Catalyst\Helpers\Debug;` | Stores and validates runtime presentation limits and theme preferences for dumps. |
| `app/Helpers/Debug/DumperPalette.php` | `DumperPalette` | `Catalyst\Helpers\Debug;` | Loads, validates, caches, and exposes dumper theme palettes. |
| `app/Helpers/Debug/DumperRenderer.php` | `DumperRenderer` | `Catalyst\Helpers\Debug;` | Renders formatted dump data as terminal text or interactive HTML output. |
| `app/Helpers/Debug/MainFormatter.php` | `MainFormatter` | `Catalyst\Helpers\Debug;` | Selects the specialized formatter that represents each inspected PHP value. |
| `app/Helpers/Debug/ThemeName.php` | `ThemeName` | `Catalyst\Helpers\Debug;` | Enumerates the supported dumper theme identifiers and validates theme-name input. |
| `app/Helpers/Debug/ThemeProviderInterface.php` | `ThemeProviderInterface` | `Catalyst\Helpers\Debug;` | Defines the palette operations required from dumper theme providers. |
| `app/Helpers/Debug/Formatters/ArrayFormatter.php` | `ArrayFormatter` | `Catalyst\Helpers\Debug\Formatters;` | Formats nested arrays while enforcing dumper depth and child-count limits. |
| `app/Helpers/Debug/Formatters/ObjectFormatter.php` | `ObjectFormatter` | `Catalyst\Helpers\Debug\Formatters;` | Formats reflected object structure while enforcing depth, size, and recursion limits. |
| `app/Helpers/Debug/Formatters/PrimitiveTypeFormatter.php` | `PrimitiveTypeFormatter` | `Catalyst\Helpers\Debug\Formatters;` | Formats scalar and null values according to dumper limits and output mode. |
| `app/Helpers/Debug/Formatters/ResourceFormatter.php` | `ResourceFormatter` | `Catalyst\Helpers\Debug\Formatters;` | Formats PHP resources with their runtime identifier and resource type. |
| `app/Helpers/Error/ErrorCatcher.php` | `ErrorCatcher` | `Catalyst\Helpers\Error;` | Registers shutdown, exception and PHP error handlers once per request. |
| `app/Helpers/Error/ErrorHandler.php` | `ErrorHandler` | `Catalyst\Helpers\Error;` | Converts reportable PHP errors into Catalyst diagnostic output. |
| `app/Helpers/Error/ErrorLogger.php` | `ErrorLogger` | `Catalyst\Helpers\Error;` | Maps captured errors to logger levels and writes structured error context. |
| `app/Helpers/Error/ErrorOutput.php` | `ErrorOutput` | `Catalyst\Helpers\Error;` | Formats caught errors for CLI boxes or web error templates. |
| `app/Helpers/Error/ExceptionHandler.php` | `ExceptionHandler` | `Catalyst\Helpers\Error;` | Converts framework exceptions into HTTP, JSON or diagnostic error responses. |
| `app/Helpers/Error/ShutdownHandler.php` | `ShutdownHandler` | `Catalyst\Helpers\Error;` | Captures fatal shutdown errors and renders them through the shared error output path. |
| `app/Helpers/Exceptions/ConnectionException.php` | `ConnectionException` | `Catalyst\Helpers\Exceptions` | Represents failures while establishing database connections. |
| `app/Helpers/Exceptions/EnvironmentException.php` | `EnvironmentException` | `Catalyst\Helpers\Exceptions` | Provides bootstrap exceptions for missing, unreadable or invalid environment files. |
| `app/Helpers/Exceptions/FileSystemException.php` | `FileSystemException` | `Catalyst\Helpers\Exceptions` | Provides exceptions for common file read, write and existence failures. |
| `app/Helpers/Exceptions/ForbiddenException.php` | `ForbiddenException` | `Catalyst\Helpers\Exceptions` | Carries denial context for role, permission and policy authorization failures. |
| `app/Helpers/Exceptions/MailException.php` | `MailException` | `Catalyst\Helpers\Exceptions` | Provides typed error codes and factories for mail delivery failures. |
| `app/Helpers/Exceptions/MethodNotAllowedException.php` | `MethodNotAllowedException` | `Catalyst\Helpers\Exceptions` | Carries the HTTP methods accepted by a route after a 405 match failure. |
| `app/Helpers/Exceptions/ModelNotFoundException.php` | `ModelNotFoundException` | `Catalyst\Helpers\Exceptions` | Carries model identity when an expected database record is absent. |
| `app/Helpers/Exceptions/OptimisticLockException.php` | `OptimisticLockException` | `Catalyst\Helpers\Exceptions` | Carries model identity and expected versus stored lock versions. |
| `app/Helpers/Exceptions/QueryException.php` | `QueryException` | `Catalyst\Helpers\Exceptions` | Represents failures while executing database queries. |
| `app/Helpers/Exceptions/RouteNotFoundException.php` | `RouteNotFoundException` | `Catalyst\Helpers\Exceptions` | Represents URI or named-route lookup failures with HTTP 404 semantics. |
| `app/Helpers/Exceptions/ValidationException.php` | `ValidationException` | `Catalyst\Helpers\Exceptions` | Carries field errors, safe old input and response metadata for failed validation. |
| `app/Helpers/Exceptions/ViewException.php` | `ViewException` | `Catalyst\Helpers\Exceptions` | Provides exceptions for missing layouts, missing templates and executable token templates. |
| `app/Helpers/I18n/TranslationLoader.php` | `TranslationLoader` | `Catalyst\Helpers\I18n` | Loads, flattens, merges and caches locale translation groups. |
| `app/Helpers/I18n/Translator.php` | `Translator` | `Catalyst\Helpers\I18n` | Resolves localized strings, lists and dates across global and module paths. |
| `app/Helpers/IO/FileOutput.php` | `FileOutput` | `Catalyst\Helpers\IO;` | Resolves CLI output targets and writes sanitized command output to files. |
| `app/Helpers/Log/LogRotator.php` | `LogRotator` | `Catalyst\Helpers\Log` | Preserves bounded log history while excluding stream destinations. |
| `app/Helpers/Log/LoggerConfigurator.php` | `LoggerConfigurator` | `Catalyst\Helpers\Log` | Creates log directories and validates configurable channel, level and rotation limits. |
| `app/Helpers/Log/LoggerContextSanitizer.php` | `LoggerContextSanitizer` | `Catalyst\Helpers\Log` | Applies resource sensitivity policies to nested and top-level logging context. |
| `app/Helpers/Log/LoggerEntryFormatter.php` | `LoggerEntryFormatter` | `Catalyst\Helpers\Log` | Adds request metadata, timestamps, client identity and serialized context to log messages. |
| `app/Helpers/Log/LoggerInlineDisplay.php` | `LoggerInlineDisplay` | `Catalyst\Helpers\Log` | Maps logger levels to CLI styles and emits formatted diagnostic output. |
| `app/Helpers/Log/LoggerLevelMap.php` | `LoggerLevelMap` | `Catalyst\Helpers\Log` | Normalizes level names and resolves filtering priorities and directories. |
| `app/Helpers/Log/LoggerRequestClassifier.php` | `LoggerRequestClassifier` | `Catalyst\Helpers\Log` | Distinguishes CLI, API, asset, bot, AJAX and page requests. |
| `app/Helpers/Log/LoggerSettings.php` | `LoggerSettings` | `Catalyst\Helpers\Log` | Stores destination, threshold, display and rotation options used by logger services. |
| `app/Helpers/Log/LoggerWriter.php` | `LoggerWriter` | `Catalyst\Helpers\Log` | Resolves log paths, creates directories, rotates files and appends entries. |
| `app/Helpers/Log/Logger.php` | `Logger` | `Catalyst\Helpers\Log;` | Filters, sanitizes, formats and writes application log events. |
| `app/Helpers/Path/ProjectPath.php` | `ProjectPath` | `Catalyst\Helpers\Path` | Centralizes paths for boot-core, cache, binaries, storage and migrations. |
| `app/Helpers/Security/CspNonce.php` | `CspNonce` | `Catalyst\Helpers\Security` | Generates and exposes the nonce shared by CSP headers and inline scripts. |
| `app/Helpers/Security/CsrfProtection.php` | `CsrfProtection` | `Catalyst\Helpers\Security` | Generates, validates, expires and renders session-backed CSRF tokens. |
| `app/Helpers/Security/SensitiveValueRedactor.php` | `SensitiveValueRedactor` | `Catalyst\Helpers\Security` | Detects sensitive key names and replaces their values before exposure. |
| `app/Helpers/ToolBox/DrawBoxCliRenderer.php` | `DrawBoxCliRenderer` | `Catalyst\Helpers\ToolBox` | Calculates terminal dimensions, wraps content and assembles styled CLI boxes. |
| `app/Helpers/ToolBox/DrawBoxFileOutputDecorator.php` | `DrawBoxFileOutputDecorator` | `Catalyst\Helpers\ToolBox` | Inserts a colored separator and centered persistence message before the box footer. |
| `app/Helpers/ToolBox/DrawBoxHtmlRenderer.php` | `DrawBoxHtmlRenderer` | `Catalyst\Helpers\ToolBox` | Builds styled HTML output with optional header, body and footer regions. |
| `app/Helpers/ToolBox/DrawBoxStylePalette.php` | `DrawBoxStylePalette` | `Catalyst\Helpers\ToolBox` | Supplies terminal escape codes and CSS class names for formatted boxes. |
| `app/Helpers/ToolBox/DrawBoxTextHelper.php` | `DrawBoxTextHelper` | `Catalyst\Helpers\ToolBox` | Preserves ANSI decoration while fitting visible text into constrained widths. |
| `app/Helpers/ToolBox/DrawBox.php` | `DrawBox` | `Catalyst\Helpers\ToolBox;` | Selects CLI or HTML rendering and optionally appends file-output status. |
| `app/Helpers/Validation/RuleParser.php` | `RuleParser` | `Catalyst\Helpers\Validation` | Normalizes string or array validation definitions into rule-and-parameter tuples. |
| `app/Helpers/Validation/ValidationRunner.php` | `ValidationRunner` | `Catalyst\Helpers\Validation` | Applies parsed validation rules to input fields and collects localized errors. |
| `app/Helpers/Validation/Validator.php` | `Validator` | `Catalyst\Helpers\Validation` | Exposes lazy validation results and field-level error collections to callers. |
| `app/Helpers/Validation/Rules/ComparisonRules.php` | `ComparisonRules` | `Catalyst\Helpers\Validation\Rules` | Validates relationships between fields and membership in allowed value sets. |
| `app/Helpers/Validation/Rules/FileRules.php` | `FileRules` | `Catalyst\Helpers\Validation\Rules` | Resolves uploaded files and validates file presence, size, extension and MIME type. |
| `app/Helpers/Validation/Rules/FormatRules.php` | `FormatRules` | `Catalyst\Helpers\Validation\Rules` | Validates common scalar formats such as email, URL, date and boolean values. |
| `app/Helpers/Validation/Rules/NumericRules.php` | `NumericRules` | `Catalyst\Helpers\Validation\Rules` | Validates numeric types and configured numeric ranges. |
| `app/Helpers/Validation/Rules/StringRules.php` | `StringRules` | `Catalyst\Helpers\Validation\Rules` | Validates required values, string lengths, character sets and patterns. |
| `app/Helpers/Validation/Rules/UniqueRule.php` | `UniqueRule` | `Catalyst\Helpers\Validation\Rules` | Checks uniqueness constraints through the database query builder. |
| `Repository/Framework/ApiPlatform/Controllers/ApiPlatformController.php` | `ApiPlatformController` | `Catalyst\Repository\ApiPlatform\Controllers` | Renders the API Platform admin surface, creates and revokes bearer tokens, |
| `Repository/Framework/ApiPlatform/Controllers/VersionApiController.php` | `VersionApiController` | `Catalyst\Repository\ApiPlatform\Controllers` | Resolves versioned resources, authorizes access, returns history, |
| `Repository/Framework/ApiPlatform/Controllers/WorkflowApiController.php` | `WorkflowApiController` | `Catalyst\Repository\ApiPlatform\Controllers` | Filters workflow instances by request criteria, enforces per-resource |
| `Repository/Framework/ApiPlatform/Requests/ApiTokenRequest.php` | `ApiTokenRequest` | `Catalyst\Repository\ApiPlatform\Requests` | Authorizes token creation, limits accepted input, validates token fields, |
| `Repository/Framework/Audit/Controllers/AuditLogController.php` | `AuditLogController` | `Catalyst\Repository\Audit\Controllers` | Builds the audit log data grid, handles CSV/XLS exports, |
| `Repository/Framework/Auth/Controllers/EmailVerificationController.php` | `EmailVerificationController` | `Catalyst\Repository\Auth\Controllers` | Validates verification tokens, consumes active token records, and marks matching users as verified. |
| `Repository/Framework/Auth/Controllers/LoginController.php` | `LoginController` | `Catalyst\Repository\Auth\Controllers` | Validates login input, protects account state checks, and creates either pending MFA state or a full session. |
| `Repository/Framework/Auth/Controllers/LogoutController.php` | `LogoutController` | `Catalyst\Repository\Auth\Controllers` | Invalidates the active auth state and returns the user to a same-origin destination with feedback. |
| `Repository/Framework/Auth/Controllers/MfaController.php` | `MfaController` | `Catalyst\Repository\Auth\Controllers` | Enforces MFA access rules, provisions TOTP secrets, persists backup-code state, and completes pending logins. |
| `Repository/Framework/Auth/Controllers/PasswordResetController.php` | `PasswordResetController` | `Catalyst\Repository\Auth\Controllers` | Issues password reset emails without account enumeration and updates credentials after token validation. |
| `Repository/Framework/Auth/Controllers/RegisterController.php` | `RegisterController` | `Catalyst\Repository\Auth\Controllers` | Validates registration input, creates unverified users, and sends one-time verification links. |
| `Repository/Framework/Auth/Controllers/SocialAuthController.php` | `SocialAuthController` | `Catalyst\Repository\Auth\Controllers` | Starts provider authorization, validates callback data, links OAuth identities, and signs in local users. |
| `Repository/Framework/Auth/Models/User.php` | `User` | `Catalyst\Repository\Auth\Models` | Represents authenticated application users for ORM reads and writes while hiding credential data. |
| `Repository/Framework/Auth/Requests/EmailVerificationTokenRequest.php` | `EmailVerificationTokenRequest` | `Catalyst\Repository\Auth\Requests` | Normalizes token input and rejects values that cannot match a 64-character verification token. |
| `Repository/Framework/Auth/Requests/MfaCodeRequest.php` | `MfaCodeRequest` | `Catalyst\Repository\Auth\Requests` | Accepts TOTP codes and, when allowed, backup-code input before controllers verify the secret. |
| `Repository/Framework/Automation/Actions/AutomationRuleExecutionService.php` | `AutomationRuleExecutionService` | `Catalyst\Repository\Automation\Actions` | Validate execution claims and delegate idempotent rule runs to the automation manager. |
| `Repository/Framework/Automation/Actions/AutomationRuleMutationService.php` | `AutomationRuleMutationService` | `Catalyst\Repository\Automation\Actions` | Update, delete, transition and restore automation rules while enforcing record claims. |
| `Repository/Framework/Automation/Controllers/AutomationRuleApiController.php` | `AutomationRuleApiController` | `Catalyst\Repository\Automation\Controllers` | Authorize API requests and serialize automation rule listings, details and run outcomes. |
| `Repository/Framework/Automation/Controllers/AutomationRuleController.php` | `AutomationRuleController` | `Catalyst\Repository\Automation\Controllers` | Render automation rule screens and coordinate authorized CRUD, execution and workflow actions. |
| `Repository/Framework/Automation/Requests/AutomationRuleIndexRequest.php` | `AutomationRuleIndexRequest` | `Catalyst\Repository\Automation\Requests` | Expose the allowed listing inputs and convert them into bounded repository criteria. |
| `Repository/Framework/Automation/Requests/AutomationRuleRequest.php` | `AutomationRuleRequest` | `Catalyst\Repository\Automation\Requests` | Authorize automation mutations and enforce rule, JSON, temporal and action constraints. |
| `Repository/Framework/Automation/Requests/AutomationRuleTransitionRequest.php` | `AutomationRuleTransitionRequest` | `Catalyst\Repository\Automation\Requests` | Expose and validate the transition key and optional operator notes. |
| `Repository/Framework/Automation/Requests/AutomationRunContextRequest.php` | `AutomationRunContextRequest` | `Catalyst\Repository\Automation\Requests` | Decode automation context JSON and expose a normalized execution payload. |
| `Repository/Framework/Automation/Support/AutomationManualRunState.php` | `AutomationManualRunState` | `Catalyst\Repository\Automation\Support` | Carry one automation run result and its context across the redirect to the detail view. |
| `Repository/Framework/Automation/Support/AutomationRuleFormFactory.php` | `AutomationRuleFormFactory` | `Catalyst\Repository\Automation\Support` | Define automation rule form sections, fields, defaults and actions for create and edit screens. |
| `Repository/Framework/Automation/Support/AutomationRuleGridFactory.php` | `AutomationRuleGridFactory` | `Catalyst\Repository\Automation\Support` | Configure rule columns, filters, row actions and repository-backed pagination. |
| `Repository/Framework/Automation/Support/AutomationRuleShowDataFactory.php` | `AutomationRuleShowDataFactory` | `Catalyst\Repository\Automation\Support` | Combine rule state, history, versions, transitions, claims and manual run context for rendering. |
| `Repository/Framework/Catalogs/Actions/CatalogMutationService.php` | `CatalogMutationService` | `Catalyst\Repository\Catalogs\Actions` | Update, delete, transition and restore catalog data while enforcing parent catalog claims. |
| `Repository/Framework/Catalogs/Controllers/CatalogController.php` | `CatalogController` | `Catalyst\Repository\Catalogs\Controllers` | Render catalog screens and coordinate authorized CRUD, workflow, version and item actions. |
| `Repository/Framework/Catalogs/Requests/CatalogDefinitionRequest.php` | `CatalogDefinitionRequest` | `Catalyst\Repository\Catalogs\Requests` | Authorize catalog mutations and enforce accepted fields, validation rules and key uniqueness. |
| `Repository/Framework/Catalogs/Requests/CatalogItemRequest.php` | `CatalogItemRequest` | `Catalyst\Repository\Catalogs\Requests` | Authorize item mutations and enforce key, temporal and metadata constraints within a catalog. |
| `Repository/Framework/Catalogs/Support/CatalogFormFactory.php` | `CatalogFormFactory` | `Catalyst\Repository\Catalogs\Support` | Define catalog form sections, fields, defaults and actions for create and edit screens. |
| `Repository/Framework/Catalogs/Support/CatalogGridFactory.php` | `CatalogGridFactory` | `Catalyst\Repository\Catalogs\Support` | Configure catalog columns, filters, row actions and repository-backed pagination. |
| `Repository/Framework/DemoUi/Controllers/DemoUiController.php` | `DemoUiController` | `Catalyst\Repository\DemoUi\Controllers` | Resolves demo routes, navigation groups and trusted generated previews. |
| `Repository/Framework/DevTools/Controllers/DatabaseResetController.php` | `DatabaseResetController` | `Catalyst\Repository\DevTools\Controllers` | Delegates destructive test-database resets to the reset service. |
| `Repository/Framework/DevTools/Controllers/DatabaseTestController.php` | `DatabaseTestController` | `Catalyst\Repository\DevTools\Controllers` | Reports connection health and configuration source for DevTools. |
| `Repository/Framework/DevTools/Controllers/FlashTestController.php` | `FlashTestController` | `Catalyst\Repository\DevTools\Controllers` | Creates, persists and clears test flash messages. |
| `Repository/Framework/DevTools/Controllers/FormEventTestController.php` | `FormEventTestController` | `Catalyst\Repository\DevTools\Controllers` | Returns deterministic save, validation, refresh and redirect test responses. |
| `Repository/Framework/DevTools/Controllers/I18nTestController.php` | `I18nTestController` | `Catalyst\Repository\DevTools\Controllers` | Reports translation samples and switches the active test locale. |
| `Repository/Framework/DevTools/Controllers/InfraTestController.php` | `InfraTestController` | `Catalyst\Repository\DevTools\Controllers` | Exercises response envelopes, escaping, logging, CORS and route caching. |
| `Repository/Framework/DevTools/Controllers/MailTestController.php` | `MailTestController` | `Catalyst\Repository\DevTools\Controllers` | Validates demo mail fields and reports the mail-manager result. |
| `Repository/Framework/DevTools/Controllers/ModalTestController.php` | `ModalTestController` | `Catalyst\Repository\DevTools\Controllers` | Supplies modal content and validates the modal form harness. |
| `Repository/Framework/DevTools/Controllers/OrmTestController.php` | `OrmTestController` | `Catalyst\Repository\DevTools\Controllers` | Validates CRUD, collection, pagination and exception flows against demo data. |
| `Repository/Framework/DevTools/Controllers/RbacTestController.php` | `RbacTestController` | `Catalyst\Repository\DevTools\Controllers` | Reports current RBAC state and assigns the demo administrator role. |
| `Repository/Framework/DevTools/Controllers/RouteTestController.php` | `RouteTestController` | `Catalyst\Repository\DevTools\Controllers` | Maps configured application entries to their canonical paths. |
| `Repository/Framework/DevTools/Controllers/TestFeaturesController.php` | `TestFeaturesController` | `Catalyst\Repository\DevTools\Controllers` | Supplies authentication and navigation state to the DevTools workspace. |
| `Repository/Framework/DevTools/Controllers/ToasterTestController.php` | `ToasterTestController` | `Catalyst\Repository\DevTools\Controllers` | Returns deterministic toaster, modal and partial-refresh responses. |
| `Repository/Framework/DevTools/Controllers/UmlController.php` | `UmlController` | `Catalyst\Repository\DevTools\Controllers` | Supplies configuration diagnostics to the trusted UML renderer. |
| `Repository/Framework/DevTools/Controllers/UploadTestController.php` | `UploadTestController` | `Catalyst\Repository\DevTools\Controllers` | Validates, stores and reports uploaded DevTools attachments. |
| `Repository/Framework/DevTools/Controllers/ValidatorTestController.php` | `ValidatorTestController` | `Catalyst\Repository\DevTools\Controllers` | Returns deterministic validation and uniqueness-check responses. |
| `Repository/Framework/DevTools/Models/DemoEmail.php` | `DemoEmail` | `Catalyst\Repository\DevTools\Models` | Provides disposable model data for CRUD and collection tests. |
| `Repository/Framework/DevTools/Services/DatabaseResetService.php` | `DatabaseResetService` | `Catalyst\Repository\DevTools\Services` | Orchestrates destructive DevTools database reset operations. |
| `Repository/Framework/Documents/Actions/DocumentTemplateExportService.php` | `DocumentTemplateExportService` | `Catalyst\Repository\Documents\Actions` | Delegate document artifact generation to the document template manager. |
| `Repository/Framework/Documents/Actions/DocumentTemplateMutationService.php` | `DocumentTemplateMutationService` | `Catalyst\Repository\Documents\Actions` | Update, delete, export, transition and restore templates while enforcing record claims. |
| `Repository/Framework/Documents/Actions/DocumentTemplatePreviewService.php` | `DocumentTemplatePreviewService` | `Catalyst\Repository\Documents\Actions` | Delegate document preview rendering to the document template manager. |
| `Repository/Framework/Documents/Controllers/DocumentTemplateApiController.php` | `DocumentTemplateApiController` | `Catalyst\Repository\Documents\Controllers` | Authorize API requests and serialize template listings, details, previews and artifacts. |
| `Repository/Framework/Documents/Controllers/DocumentTemplateController.php` | `DocumentTemplateController` | `Catalyst\Repository\Documents\Controllers` | Render template screens and coordinate authorized CRUD, preview, export and workflow actions. |
| `Repository/Framework/Documents/Requests/DocumentExportPayloadRequest.php` | `DocumentExportPayloadRequest` | `Catalyst\Repository\Documents\Requests` | Provide the normalized render payload required to export a document artifact. |
| `Repository/Framework/Documents/Requests/DocumentPreviewPayloadRequest.php` | `DocumentPreviewPayloadRequest` | `Catalyst\Repository\Documents\Requests` | Decode document payload JSON and expose a normalized render context. |
| `Repository/Framework/Documents/Requests/DocumentTemplateIndexRequest.php` | `DocumentTemplateIndexRequest` | `Catalyst\Repository\Documents\Requests` | Convert listing inputs into bounded repository criteria. |
| `Repository/Framework/Documents/Requests/DocumentTemplateRequest.php` | `DocumentTemplateRequest` | `Catalyst\Repository\Documents\Requests` | Authorize template mutations and enforce accepted fields, JSON schemas and slug uniqueness. |
| `Repository/Framework/Documents/Requests/DocumentTemplateTransitionRequest.php` | `DocumentTemplateTransitionRequest` | `Catalyst\Repository\Documents\Requests` | Expose and validate the transition key and optional operator notes. |
| `Repository/Framework/Documents/Support/DocumentPreviewState.php` | `DocumentPreviewState` | `Catalyst\Repository\Documents\Support` | Carry one rendered preview and its payload across the redirect to the detail view. |
| `Repository/Framework/Documents/Support/DocumentTemplateFormFactory.php` | `DocumentTemplateFormFactory` | `Catalyst\Repository\Documents\Support` | Define template form sections, fields, defaults and actions for create and edit screens. |
| `Repository/Framework/Documents/Support/DocumentTemplateGridFactory.php` | `DocumentTemplateGridFactory` | `Catalyst\Repository\Documents\Support` | Configure template columns, filters, row actions and repository-backed pagination. |
| `Repository/Framework/Documents/Support/DocumentTemplateShowDataFactory.php` | `DocumentTemplateShowDataFactory` | `Catalyst\Repository\Documents\Support` | Combine template state, previews, artifacts, versions, transitions and claims for rendering. |
| `Repository/Framework/Media/Controllers/MediaLibraryController.php` | `MediaLibraryController` | `Catalyst\Repository\Media\Controllers` | Render media screens and coordinate authorized upload, edit, deletion, bulk and export actions. |
| `Repository/Framework/Media/Controllers/MetadataFieldController.php` | `MetadataFieldController` | `Catalyst\Repository\Media\Controllers` | Render metadata field screens and coordinate authorized CRUD and export actions. |
| `Repository/Framework/Media/Requests/MediaBulkSelectionRequest.php` | `MediaBulkSelectionRequest` | `Catalyst\Repository\Media\Requests` | Normalize submitted selections into a positive, ordered identifier list. |
| `Repository/Framework/Media/Requests/MediaItemRequest.php` | `MediaItemRequest` | `Catalyst\Repository\Media\Requests` | Authorize media mutations and enforce upload, storage and dynamic metadata constraints. |
| `Repository/Framework/Media/Requests/MetadataFieldDefinitionRequest.php` | `MetadataFieldDefinitionRequest` | `Catalyst\Repository\Media\Requests` | Authorize metadata definition mutations and enforce resource, type and field configuration rules. |
| `Repository/Framework/Media/Support/MediaLibraryFormFactory.php` | `MediaLibraryFormFactory` | `Catalyst\Repository\Media\Support` | Combine storage options and dynamic metadata definitions into the media asset form. |
| `Repository/Framework/Media/Support/MetadataFieldFormFactory.php` | `MetadataFieldFormFactory` | `Catalyst\Repository\Media\Support` | Configure metadata field sections, values, dynamic controls and form actions. |
| `Repository/Framework/Notification/Controllers/NotificationController.php` | `NotificationController` | `Catalyst\Repository\Notification\Controllers` | Coordinates notification API responses for the current user. |
| `Repository/Framework/Notification/Controllers/PresenceController.php` | `PresenceController` | `Catalyst\Repository\Notification\Controllers` | Refreshes presence state and reports claim conflicts to clients. |
| `Repository/Framework/Operations/Controllers/AbstractOperationsController.php` | `AbstractOperationsController` | `Catalyst\Repository\Operations\Controllers` | Centralizes reusable operations-controller presentation helpers. |
| `Repository/Framework/Operations/Controllers/AppearanceController.php` | `AppearanceController` | `Catalyst\Repository\Operations\Controllers` | Renders and persists administrator-controlled appearance configuration. |
| `Repository/Framework/Operations/Controllers/DeploymentsController.php` | `DeploymentsController` | `Catalyst\Repository\Operations\Controllers` | Connects deployment administration forms and grids to deployment services. |
| `Repository/Framework/Operations/Controllers/FeatureFlagsController.php` | `FeatureFlagsController` | `Catalyst\Repository\Operations\Controllers` | Presents flag administration state and persists approved mutations. |
| `Repository/Framework/Operations/Controllers/LocalizationController.php` | `LocalizationController` | `Catalyst\Repository\Operations\Controllers` | Presents locale diagnostics and applies locale administration actions. |
| `Repository/Framework/Operations/Controllers/ModuleDesignerController.php` | `ModuleDesignerController` | `Catalyst\Repository\Operations\Controllers` | Manages module-designer requests, redirects and session-backed results. |
| `Repository/Framework/Operations/Controllers/OperationsOverviewController.php` | `OperationsOverviewController` | `Catalyst\Repository\Operations\Controllers` | Aggregates operational summaries for the administration dashboard. |
| `Repository/Framework/Operations/Controllers/PluginsController.php` | `PluginsController` | `Catalyst\Repository\Operations\Controllers` | Connects plugin administration pages to the plugin manager. |
| `Repository/Framework/Operations/Controllers/TenancyController.php` | `TenancyController` | `Catalyst\Repository\Operations\Controllers` | Exposes tenancy diagnostics to platform administrators. |
| `Repository/Framework/Operations/Requests/AppearanceUpdateRequest.php` | `AppearanceUpdateRequest` | `Catalyst\Repository\Operations\Requests` | Provides normalized appearance input to the operations controller. |
| `Repository/Framework/Operations/Requests/DeploymentRunRequest.php` | `DeploymentRunRequest` | `Catalyst\Repository\Operations\Requests` | Authorizes and constrains deployment profile and dry-run fields. |
| `Repository/Framework/Operations/Requests/FeatureFlagDefaultRequest.php` | `FeatureFlagDefaultRequest` | `Catalyst\Repository\Operations\Requests` | Supplies the requested default flag state to the controller. |
| `Repository/Framework/Operations/Requests/FeatureFlagOverrideRequest.php` | `FeatureFlagOverrideRequest` | `Catalyst\Repository\Operations\Requests` | Authorizes and constrains feature-flag override mutations. |
| `Repository/Framework/Operations/Requests/LocaleCreateRequest.php` | `LocaleCreateRequest` | `Catalyst\Repository\Operations\Requests` | Supplies normalized locale creation fields to the controller. |
| `Repository/Framework/Operations/Requests/LocaleSyncRequest.php` | `LocaleSyncRequest` | `Catalyst\Repository\Operations\Requests` | Supplies normalized locale synchronization fields to the controller. |
| `Repository/Framework/Operations/Requests/LocalizationSettingsRequest.php` | `LocalizationSettingsRequest` | `Catalyst\Repository\Operations\Requests` | Supplies normalized locale settings to the localization controller. |
| `Repository/Framework/Operations/Requests/ModuleDesignerRequest.php` | `ModuleDesignerRequest` | `Catalyst\Repository\Operations\Requests` | Provides the normalized designer form state consumed by scaffolding. |
| `Repository/Framework/Operations/Requests/Concerns/NormalizesCheckboxValues.php` | `NormalizesCheckboxValues` | `Catalyst\Repository\Operations\Requests\Concerns` | Shares consistent checkbox coercion across operations requests. |
| `Repository/Framework/Roles/Controllers/PermissionsController.php` | `PermissionsController` | `Catalyst\Repository\Roles\Controllers` | Lists, creates, updates and deletes permission records while enforcing authorization and record claims. |
| `Repository/Framework/Roles/Controllers/RolesController.php` | `RolesController` | `Catalyst\Repository\Roles\Controllers` | Lists and mutates roles, renders role forms and synchronizes permission assignments with record-claim protection. |
| `Repository/Framework/Roles/Controllers/UserManagementController.php` | `UserManagementController` | `Catalyst\Repository\Roles\Controllers` | Lists users, handles exports and enrolls new accounts with validated role assignment. |
| `Repository/Framework/Roles/Controllers/UserRolesController.php` | `UserRolesController` | `Catalyst\Repository\Roles\Controllers` | Displays assignable roles and applies role additions or removals for active users. |
| `Repository/Framework/Roles/Requests/PermissionBulkSelectionRequest.php` | `PermissionBulkSelectionRequest` | `Catalyst\Repository\Roles\Requests` | Normalizes submitted permission identifiers and exposes the underlying HTTP request. |
| `Repository/Framework/Roles/Requests/PermissionPayloadRequest.php` | `PermissionPayloadRequest` | `Catalyst\Repository\Roles\Requests` | Authorizes permission mutations and defines accepted fields, validation rules and labels. |
| `Repository/Framework/Roles/Requests/RoleBulkSelectionRequest.php` | `RoleBulkSelectionRequest` | `Catalyst\Repository\Roles\Requests` | Normalizes submitted role identifiers and exposes the underlying HTTP request. |
| `Repository/Framework/Roles/Requests/RolePayloadRequest.php` | `RolePayloadRequest` | `Catalyst\Repository\Roles\Requests` | Authorizes role mutations and defines accepted fields, validation rules and labels. |
| `Repository/Framework/Roles/Requests/RolePermissionSyncRequest.php` | `RolePermissionSyncRequest` | `Catalyst\Repository\Roles\Requests` | Normalizes the selected permission identifiers and exposes the underlying HTTP request. |
| `Repository/Framework/Roles/Requests/UserEnrollmentRequest.php` | `UserEnrollmentRequest` | `Catalyst\Repository\Roles\Requests` | Normalizes enrollment fields, reports validation errors and retains only replay-safe input. |
| `Repository/Framework/Roles/Support/RbacLabelPresenter.php` | `RbacLabelPresenter` | `Catalyst\Repository\Roles\Support` | Presents role and permission names, descriptions and lists with stable normalized lookup keys. |
| `Repository/Framework/Roles/Support/UserEnrollmentFormFactory.php` | `UserEnrollmentFormFactory` | `Catalyst\Repository\Roles\Support` | Resolves selectable roles and assembles the form schema consumed by the admin surface. |
| `Repository/Framework/Settings/Controllers/AppConfigSaveController.php` | `AppConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated application configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/CacheConfigSaveController.php` | `CacheConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated cache configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/ConfigController.php` | `ConfigController` | `Catalyst\Repository\Settings\Controllers` | Loads configurable sections, evaluates administrator readiness and exposes the canonical setup route. |
| `Repository/Framework/Settings/Controllers/CorsConfigSaveController.php` | `CorsConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Validates and persists CORS policy values submitted by the setup surface. |
| `Repository/Framework/Settings/Controllers/DbConfigSaveController.php` | `DbConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated database writes and surfaces connectivity warnings without discarding saved configuration. |
| `Repository/Framework/Settings/Controllers/DevToolsConfigSaveController.php` | `DevToolsConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated debug and log-display writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/DkimController.php` | `DkimController` | `Catalyst\Repository\Settings\Controllers` | Validates DKIM input, invokes key generation and returns the DNS record payload. |
| `Repository/Framework/Settings/Controllers/FtpConfigController.php` | `FtpConfigController` | `Catalyst\Repository\Settings\Controllers` | Validates transfer settings, preserves stored credentials and runs upload-cleanup connectivity pretests. |
| `Repository/Framework/Settings/Controllers/HealthController.php` | `HealthController` | `Catalyst\Repository\Settings\Controllers` | Builds health reports for the admin panel, liveness endpoint and readiness endpoint. |
| `Repository/Framework/Settings/Controllers/LoggingConfigSaveController.php` | `LoggingConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated logging configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/MailConfigSaveController.php` | `MailConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated mail configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/SecurityConfigSaveController.php` | `SecurityConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated security configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/SessionConfigSaveController.php` | `SessionConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated session configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Controllers/SetupCompletionController.php` | `SetupCompletionController` | `Catalyst\Repository\Settings\Controllers` | Creates the first administrator, validates setup readiness and toggles the configured state. |
| `Repository/Framework/Settings/Controllers/WebSocketConfigSaveController.php` | `WebSocketConfigSaveController` | `Catalyst\Repository\Settings\Controllers` | Delegates validated WebSocket configuration writes and returns the setup AJAX response. |
| `Repository/Framework/Settings/Requests/AbstractSettingsRequest.php` | `AbstractSettingsRequest` | `Catalyst\Repository\Settings\Requests` | Normalizes scalar and boolean inputs and removes secrets from replayable validation state. |
| `Repository/Framework/Settings/Requests/AppConfigRequest.php` | `AppConfigRequest` | `Catalyst\Repository\Settings\Requests` | Validates application metadata, entry points and locale choices before persistence. |
| `Repository/Framework/Settings/Requests/CacheConfigRequest.php` | `CacheConfigRequest` | `Catalyst\Repository\Settings\Requests` | Restricts cache activation to production and converts submitted cache flags to booleans. |
| `Repository/Framework/Settings/Requests/DbConfigRequest.php` | `DbConfigRequest` | `Catalyst\Repository\Settings\Requests` | Defines database connection rules and builds normalized connection input. |
| `Repository/Framework/Settings/Requests/DevToolsConfigRequest.php` | `DevToolsConfigRequest` | `Catalyst\Repository\Settings\Requests` | Converts submitted debug and log-display flags into normalized configuration values. |
| `Repository/Framework/Settings/Requests/LoggingConfigRequest.php` | `LoggingConfigRequest` | `Catalyst\Repository\Settings\Requests` | Defines logging channel, level and rotation constraints and normalizes submitted values. |
| `Repository/Framework/Settings/Requests/MailConfigRequest.php` | `MailConfigRequest` | `Catalyst\Repository\Settings\Requests` | Defines SMTP connection and sender rules and normalizes submitted values. |
| `Repository/Framework/Settings/Requests/SecurityConfigRequest.php` | `SecurityConfigRequest` | `Catalyst\Repository\Settings\Requests` | Constrains password hashing cost and normalizes framework-wide MFA activation. |
| `Repository/Framework/Settings/Requests/SessionConfigRequest.php` | `SessionConfigRequest` | `Catalyst\Repository\Settings\Requests` | Defines session storage and cookie constraints and normalizes submitted values. |
| `Repository/Framework/Settings/Requests/WebSocketConfigRequest.php` | `WebSocketConfigRequest` | `Catalyst\Repository\Settings\Requests` | Defines WebSocket bind, publisher and port constraints and normalizes submitted values. |
| `Repository/Framework/Settings/Services/SetupAdminProvisioner.php` | `SetupAdminProvisioner` | `Catalyst\Repository\Settings\Services` | Detects active administrators, ensures the admin role and creates the first privileged account. |
| `Repository/Framework/Settings/Services/SetupDatabaseException.php` | `SetupDatabaseException` | `Catalyst\Repository\Settings\Services` | Preserves a translation key, response status, diagnostic detail and previous failure for setup responses. |
| `Repository/Framework/Settings/Services/SetupDatabaseService.php` | `SetupDatabaseService` | `Catalyst\Repository\Settings\Services` | Verifies config files, creates the database when absent and ensures the minimal authentication schema exists. |
| `Repository/Framework/Settings/Support/AdminReadinessProbe.php` | `AdminReadinessProbe` | `Catalyst\Repository\Settings\Support` | Performs a non-throwing readiness check used while rendering the setup surface. |
| `Repository/Framework/Settings/Support/AppConfigWriter.php` | `AppConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists project metadata while preserving the setup-completion flag. |
| `Repository/Framework/Settings/Support/CacheConfigWriter.php` | `CacheConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists cache flags, refreshes cache services and clears disabled cache layers. |
| `Repository/Framework/Settings/Support/DbConfigWriter.php` | `DbConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists database credentials, retaining an unchanged password, and returns connection readiness. |
| `Repository/Framework/Settings/Support/DbConnectivityProbe.php` | `DbConnectivityProbe` | `Catalyst\Repository\Settings\Support` | Distinguishes a usable connection, a missing database and an unreachable server. |
| `Repository/Framework/Settings/Support/DevToolsConfigWriter.php` | `DevToolsConfigWriter` | `Catalyst\Repository\Settings\Support` | Mirrors debug and log-display flags into their canonical sections and the deprecated compatibility section. |
| `Repository/Framework/Settings/Support/FtpConnectionProbe.php` | `FtpConnectionProbe` | `Catalyst\Repository\Settings\Support` | Runs FTP, FTPS or SFTP upload-cleanup pretests and reports cleanup warnings. |
| `Repository/Framework/Settings/Support/LoggingConfigWriter.php` | `LoggingConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists the selected logging channel, severity and bounded rotation limits. |
| `Repository/Framework/Settings/Support/MailConfigWriter.php` | `MailConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists SMTP and sender configuration while retaining an unchanged password. |
| `Repository/Framework/Settings/Support/SecurityConfigWriter.php` | `SecurityConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists password hashing cost and framework-wide MFA activation. |
| `Repository/Framework/Settings/Support/SessionConfigWriter.php` | `SessionConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists storage and cookie settings and seeds the current session with its active backend. |
| `Repository/Framework/Settings/Support/SettingsCardFactory.php` | `SettingsCardFactory` | `Catalyst\Repository\Settings\Support` | Maps current configuration into read-only cards and groups them for the setup overview. |
| `Repository/Framework/Settings/Support/SettingsDisplayFactory.php` | `SettingsDisplayFactory` | `Catalyst\Repository\Settings\Support` | Produces view-ready rows, fields, modal descriptors and selected-option state. |
| `Repository/Framework/Settings/Support/SettingsModalFactory.php` | `SettingsModalFactory` | `Catalyst\Repository\Settings\Support` | Maps current configuration values into the modal forms rendered by the setup surface. |
| `Repository/Framework/Settings/Support/SettingsPageViewContext.php` | `SettingsPageViewContext` | `Catalyst\Repository\Settings\Support` | Exposes setup sections, translated notices and option maps without leaking view assembly concerns. |
| `Repository/Framework/Settings/Support/WebSocketConfigWriter.php` | `WebSocketConfigWriter` | `Catalyst\Repository\Settings\Support` | Persists activation, bind address, ports and publisher URL for the WebSocket runtime. |
