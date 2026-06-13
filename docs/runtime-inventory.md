# Runtime Inventory

> Auto-generated from filesystem and PHP tokens. Run `php public/cli.php docs:inventory` to refresh.
> Last generated: 2026-06-13 19:00:41

## Summary

- Symbols: 713
- Templates: 177
- Scripts: 56

## Symbol Roots

| Root | Count |
|---|---:|
| `Repository` | 184 |
| `app` | 529 |

## Template Roots

| Root | Count |
|---|---:|
| `Repository views` | 152 |
| `boot-core/template` | 25 |

## Script Roots

| Root | Count |
|---|---:|
| `Repository front` | 12 |
| `public catalyst js` | 44 |

## Symbols

| FQN | Type | File | Line |
|---|---|---|---:|
| `App\Repositories\UserProfileRepository` | `class` | `Repository/App/Repositories/UserProfileRepository.php` | 45 |
| `App\Services\ApplicationEntryService` | `class` | `Repository/App/Services/ApplicationEntryService.php` | 43 |
| `App\Services\UserProfileService` | `class` | `Repository/App/Services/UserProfileService.php` | 42 |
| `App\Support\PublicSurface\Controllers\PublicPageController` | `class` | `Repository/App/Support/PublicSurface/Controllers/PublicPageController.php` | 45 |
| `App\Support\PublicSurface\Support\PublicDemoCatalog` | `class` | `Repository/App/Support/PublicSurface/Support/PublicDemoCatalog.php` | 39 |
| `App\Surface\Dashboard\Controllers\DashboardController` | `class` | `Repository/App/Surface/Dashboard/Controllers/DashboardController.php` | 46 |
| `App\Surface\Home\Controllers\HomeController` | `class` | `Repository/App/Surface/Home/Controllers/HomeController.php` | 45 |
| `App\Surface\Landing\Controllers\LandingController` | `class` | `Repository/App/Surface/Landing/Controllers/LandingController.php` | 44 |
| `App\Surface\Store\Controllers\StoreController` | `class` | `Repository/App/Surface/Store/Controllers/StoreController.php` | 44 |
| `Catalyst\Entities\ApiToken` | `class` | `app/Entities/ApiToken.php` | 44 |
| `Catalyst\Entities\AuditLogEntry` | `class` | `app/Entities/AuditLogEntry.php` | 42 |
| `Catalyst\Entities\AutomationExecutionLog` | `class` | `app/Entities/AutomationExecutionLog.php` | 43 |
| `Catalyst\Entities\AutomationRule` | `class` | `app/Entities/AutomationRule.php` | 45 |
| `Catalyst\Entities\CatalogDefinition` | `class` | `app/Entities/CatalogDefinition.php` | 45 |
| `Catalyst\Entities\CatalogItem` | `class` | `app/Entities/CatalogItem.php` | 45 |
| `Catalyst\Entities\ContentVersion` | `class` | `app/Entities/ContentVersion.php` | 42 |
| `Catalyst\Entities\DeploymentRun` | `class` | `app/Entities/DeploymentRun.php` | 43 |
| `Catalyst\Entities\DocumentArtifact` | `class` | `app/Entities/DocumentArtifact.php` | 44 |
| `Catalyst\Entities\DocumentTemplate` | `class` | `app/Entities/DocumentTemplate.php` | 45 |
| `Catalyst\Entities\EventEnvelope` | `class` | `app/Entities/EventEnvelope.php` | 42 |
| `Catalyst\Entities\FeatureFlagOverride` | `class` | `app/Entities/FeatureFlagOverride.php` | 43 |
| `Catalyst\Entities\IdempotencyKey` | `class` | `app/Entities/IdempotencyKey.php` | 43 |
| `Catalyst\Entities\MediaItem` | `class` | `app/Entities/MediaItem.php` | 44 |
| `Catalyst\Entities\MetadataFieldDefinition` | `class` | `app/Entities/MetadataFieldDefinition.php` | 45 |
| `Catalyst\Entities\MetadataFieldValue` | `class` | `app/Entities/MetadataFieldValue.php` | 44 |
| `Catalyst\Entities\NotificationDispatch` | `class` | `app/Entities/NotificationDispatch.php` | 39 |
| `Catalyst\Entities\QueuedJobRecord` | `class` | `app/Entities/QueuedJobRecord.php` | 39 |
| `Catalyst\Entities\RecordClaim` | `class` | `app/Entities/RecordClaim.php` | 45 |
| `Catalyst\Entities\ReportRun` | `class` | `app/Entities/ReportRun.php` | 44 |
| `Catalyst\Entities\ResourceAttachment` | `class` | `app/Entities/ResourceAttachment.php` | 44 |
| `Catalyst\Entities\ScheduledTask` | `class` | `app/Entities/ScheduledTask.php` | 42 |
| `Catalyst\Entities\TimelineEvent` | `class` | `app/Entities/TimelineEvent.php` | 43 |
| `Catalyst\Entities\UserProfile` | `class` | `app/Entities/UserProfile.php` | 47 |
| `Catalyst\Entities\WorkflowInstance` | `class` | `app/Entities/WorkflowInstance.php` | 44 |
| `Catalyst\Entities\WorkflowTransition` | `class` | `app/Entities/WorkflowTransition.php` | 42 |
| `Catalyst\Framework\Api\ApiCatalog` | `class` | `app/Framework/Api/ApiCatalog.php` | 39 |
| `Catalyst\Framework\Api\ApiTokenManager` | `class` | `app/Framework/Api/ApiTokenManager.php` | 44 |
| `Catalyst\Framework\Api\ApiTokenRepository` | `class` | `app/Framework/Api/ApiTokenRepository.php` | 46 |
| `Catalyst\Framework\Appearance\PlatformAppearanceManager` | `class` | `app/Framework/Appearance/PlatformAppearanceManager.php` | 45 |
| `Catalyst\Framework\Argument\Argument` | `class` | `app/Framework/Argument/Argument.php` | 41 |
| `Catalyst\Framework\Argument\ArgumentBag` | `class` | `app/Framework/Argument/ArgumentBag.php` | 39 |
| `Catalyst\Framework\Argument\ArgumentParser` | `class` | `app/Framework/Argument/ArgumentParser.php` | 39 |
| `Catalyst\Framework\Argument\Option` | `class` | `app/Framework/Argument/Option.php` | 39 |
| `Catalyst\Framework\Argument\Parameter` | `class` | `app/Framework/Argument/Parameter.php` | 39 |
| `Catalyst\Framework\Argument\Validator` | `class` | `app/Framework/Argument/Validator.php` | 39 |
| `Catalyst\Framework\Attachment\AttachmentManager` | `class` | `app/Framework/Attachment/AttachmentManager.php` | 47 |
| `Catalyst\Framework\Attachment\AttachmentPolicy` | `class` | `app/Framework/Attachment/AttachmentPolicy.php` | 39 |
| `Catalyst\Framework\Attachment\AttachmentPolicyValidator` | `class` | `app/Framework/Attachment/AttachmentPolicyValidator.php` | 39 |
| `Catalyst\Framework\Attachment\AttachmentRepository` | `class` | `app/Framework/Attachment/AttachmentRepository.php` | 43 |
| `Catalyst\Framework\Attachment\AttachmentVerificationSigner` | `class` | `app/Framework/Attachment/AttachmentVerificationSigner.php` | 41 |
| `Catalyst\Framework\Audit\AuditLogManager` | `class` | `app/Framework/Audit/AuditLogManager.php` | 50 |
| `Catalyst\Framework\Audit\AuditLogRepository` | `class` | `app/Framework/Audit/AuditLogRepository.php` | 45 |
| `Catalyst\Framework\Auth\AuthInputGuard` | `class` | `app/Framework/Auth/AuthInputGuard.php` | 41 |
| `Catalyst\Framework\Auth\AuthManager` | `class` | `app/Framework/Auth/AuthManager.php` | 45 |
| `Catalyst\Framework\Auth\MfaManager` | `class` | `app/Framework/Auth/MfaManager.php` | 41 |
| `Catalyst\Framework\Auth\OAuthManager` | `class` | `app/Framework/Auth/OAuthManager.php` | 49 |
| `Catalyst\Framework\Auth\OAuth\GitHubProvider` | `class` | `app/Framework/Auth/OAuth/GitHubProvider.php` | 44 |
| `Catalyst\Framework\Auth\OAuth\GoogleProvider` | `class` | `app/Framework/Auth/OAuth/GoogleProvider.php` | 44 |
| `Catalyst\Framework\Auth\OAuth\OAuthUser` | `class` | `app/Framework/Auth/OAuth/OAuthUser.php` | 41 |
| `Catalyst\Framework\Auth\RememberMe` | `class` | `app/Framework/Auth/RememberMe.php` | 44 |
| `Catalyst\Framework\Auth\TokenRepository` | `class` | `app/Framework/Auth/TokenRepository.php` | 44 |
| `Catalyst\Framework\Auth\UserDirectoryRepository` | `class` | `app/Framework/Auth/UserDirectoryRepository.php` | 45 |
| `Catalyst\Framework\Auth\UserProvider` | `class` | `app/Framework/Auth/UserProvider.php` | 48 |
| `Catalyst\Framework\Authorization\AbilitySubject` | `class` | `app/Framework/Authorization/AbilitySubject.php` | 39 |
| `Catalyst\Framework\Authorization\AccountRecoveryPermissionMigrator` | `class` | `app/Framework/Authorization/AccountRecoveryPermissionMigrator.php` | 10 |
| `Catalyst\Framework\Authorization\ApiManagementPermissionMigrator` | `class` | `app/Framework/Authorization/ApiManagementPermissionMigrator.php` | 10 |
| `Catalyst\Framework\Authorization\CanonicalPermissionGrantMigrator` | `class` | `app/Framework/Authorization/CanonicalPermissionGrantMigrator.php` | 15 |
| `Catalyst\Framework\Authorization\Gate` | `class` | `app/Framework/Authorization/Gate.php` | 45 |
| `Catalyst\Framework\Authorization\LegacyOperationsPermissionRetirer` | `class` | `app/Framework/Authorization/LegacyOperationsPermissionRetirer.php` | 13 |
| `Catalyst\Framework\Authorization\LegacyWorkspacePermissionRetirer` | `class` | `app/Framework/Authorization/LegacyWorkspacePermissionRetirer.php` | 10 |
| `Catalyst\Framework\Authorization\PermissionRegistry` | `class` | `app/Framework/Authorization/PermissionRegistry.php` | 41 |
| `Catalyst\Framework\Authorization\Policy` | `class` | `app/Framework/Authorization/Policy.php` | 39 |
| `Catalyst\Framework\Authorization\RbacAuditLogger` | `class` | `app/Framework/Authorization/RbacAuditLogger.php` | 41 |
| `Catalyst\Framework\Authorization\RbacCacheInvalidator` | `class` | `app/Framework/Authorization/RbacCacheInvalidator.php` | 41 |
| `Catalyst\Framework\Authorization\RbacSortResolver` | `class` | `app/Framework/Authorization/RbacSortResolver.php` | 39 |
| `Catalyst\Framework\Authorization\ResourcePolicy` | `class` | `app/Framework/Authorization/ResourcePolicy.php` | 39 |
| `Catalyst\Framework\Authorization\RoleRepository` | `class` | `app/Framework/Authorization/RoleRepository.php` | 47 |
| `Catalyst\Framework\Automation\AutomationManager` | `class` | `app/Framework/Automation/AutomationManager.php` | 54 |
| `Catalyst\Framework\Automation\AutomationRuleRepository` | `class` | `app/Framework/Automation/AutomationRuleRepository.php` | 52 |
| `Catalyst\Framework\Automation\Jobs\RunScheduledAutomationRulesJob` | `class` | `app/Framework/Automation/Jobs/RunScheduledAutomationRulesJob.php` | 42 |
| `Catalyst\Framework\Cache\ArrayCacheStore` | `class` | `app/Framework/Cache/ArrayCacheStore.php` | 39 |
| `Catalyst\Framework\Cache\BootstrapCacheManager` | `class` | `app/Framework/Cache/BootstrapCacheManager.php` | 39 |
| `Catalyst\Framework\Cache\CacheManager` | `class` | `app/Framework/Cache/CacheManager.php` | 41 |
| `Catalyst\Framework\Cache\CacheSettings` | `class` | `app/Framework/Cache/CacheSettings.php` | 39 |
| `Catalyst\Framework\Cache\CacheStoreInterface` | `interface` | `app/Framework/Cache/CacheStoreInterface.php` | 39 |
| `Catalyst\Framework\Cache\FileCacheStore` | `class` | `app/Framework/Cache/FileCacheStore.php` | 41 |
| `Catalyst\Framework\Cache\NullCacheStore` | `class` | `app/Framework/Cache/NullCacheStore.php` | 39 |
| `Catalyst\Framework\Calendar\CalendarEvent` | `class` | `app/Framework/Calendar/CalendarEvent.php` | 13 |
| `Catalyst\Framework\Calendar\CalendarManager` | `class` | `app/Framework/Calendar/CalendarManager.php` | 15 |
| `Catalyst\Framework\Calendar\CalendarProviderInterface` | `interface` | `app/Framework/Calendar/CalendarProviderInterface.php` | 13 |
| `Catalyst\Framework\Calendar\CalendarQuery` | `class` | `app/Framework/Calendar/CalendarQuery.php` | 16 |
| `Catalyst\Framework\Catalog\CatalogItemAvailabilityDecorator` | `class` | `app/Framework/Catalog/CatalogItemAvailabilityDecorator.php` | 41 |
| `Catalyst\Framework\Catalog\CatalogManager` | `class` | `app/Framework/Catalog/CatalogManager.php` | 50 |
| `Catalyst\Framework\Catalog\CatalogOptionMapBuilder` | `class` | `app/Framework/Catalog/CatalogOptionMapBuilder.php` | 39 |
| `Catalyst\Framework\Catalog\CatalogRepository` | `class` | `app/Framework/Catalog/CatalogRepository.php` | 47 |
| `Catalyst\Framework\Cli\AbstractCommand` | `class` | `app/Framework/Cli/AbstractCommand.php` | 40 |
| `Catalyst\Framework\Cli\CliKernel` | `class` | `app/Framework/Cli/CliKernel.php` | 42 |
| `Catalyst\Framework\Cli\CliRouteLoader` | `class` | `app/Framework/Cli/CliRouteLoader.php` | 44 |
| `Catalyst\Framework\Cli\CommandInterface` | `interface` | `app/Framework/Cli/CommandInterface.php` | 44 |
| `Catalyst\Framework\Cli\CommandRegistry` | `class` | `app/Framework/Cli/CommandRegistry.php` | 42 |
| `Catalyst\Framework\Cli\Commands\ApiTokensSmokeCommand` | `class` | `app/Framework/Cli/Commands/ApiTokensSmokeCommand.php` | 49 |
| `Catalyst\Framework\Cli\Commands\AttachmentsListCommand` | `class` | `app/Framework/Cli/Commands/AttachmentsListCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\AttachmentsPolicySmokeCommand` | `class` | `app/Framework/Cli/Commands/AttachmentsPolicySmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\AttachmentsSmokeCommand` | `class` | `app/Framework/Cli/Commands/AttachmentsSmokeCommand.php` | 55 |
| `Catalyst\Framework\Cli\Commands\AutomationMvcRegressionCommand` | `class` | `app/Framework/Cli/Commands/AutomationMvcRegressionCommand.php` | 43 |
| `Catalyst\Framework\Cli\Commands\CacheBuildCommand` | `class` | `app/Framework/Cli/Commands/CacheBuildCommand.php` | 49 |
| `Catalyst\Framework\Cli\Commands\CacheClearCommand` | `class` | `app/Framework/Cli/Commands/CacheClearCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\CalendarSmokeCommand` | `class` | `app/Framework/Cli/Commands/CalendarSmokeCommand.php` | 23 |
| `Catalyst\Framework\Cli\Commands\CatalogsSmokeCommand` | `class` | `app/Framework/Cli/Commands/CatalogsSmokeCommand.php` | 55 |
| `Catalyst\Framework\Cli\Commands\ClaimsListCommand` | `class` | `app/Framework/Cli/Commands/ClaimsListCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ClaimsReleaseCommand` | `class` | `app/Framework/Cli/Commands/ClaimsReleaseCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\ConcurrencySmokeCommand` | `class` | `app/Framework/Cli/Commands/ConcurrencySmokeCommand.php` | 49 |
| `Catalyst\Framework\Cli\Commands\ConfigContractSmokeCommand` | `class` | `app/Framework/Cli/Commands/ConfigContractSmokeCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ConfigE2eReadinessCommand` | `class` | `app/Framework/Cli/Commands/ConfigE2eReadinessCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ConfigSecretsSyncCommand` | `class` | `app/Framework/Cli/Commands/ConfigSecretsSyncCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ConfigShowCommand` | `class` | `app/Framework/Cli/Commands/ConfigShowCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\ConfigSyncCommand` | `class` | `app/Framework/Cli/Commands/ConfigSyncCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ConfigurationFeatureFlagsSmokeCommand` | `class` | `app/Framework/Cli/Commands/ConfigurationFeatureFlagsSmokeCommand.php` | 18 |
| `Catalyst\Framework\Cli\Commands\ConfigurationLocalizationSmokeCommand` | `class` | `app/Framework/Cli/Commands/ConfigurationLocalizationSmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\ConfigurationRequestsRegressionCommand` | `class` | `app/Framework/Cli/Commands/ConfigurationRequestsRegressionCommand.php` | 13 |
| `Catalyst\Framework\Cli\Commands\CrudScaffoldSmokeCommand` | `class` | `app/Framework/Cli/Commands/CrudScaffoldSmokeCommand.php` | 14 |
| `Catalyst\Framework\Cli\Commands\DeletionSmokeCommand` | `class` | `app/Framework/Cli/Commands/DeletionSmokeCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\DeployListCommand` | `class` | `app/Framework/Cli/Commands/DeployListCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\DeployRunCommand` | `class` | `app/Framework/Cli/Commands/DeployRunCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\DevToolsDisableCommand` | `class` | `app/Framework/Cli/Commands/DevToolsDisableCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\DistributionSmokeCommand` | `class` | `app/Framework/Cli/Commands/DistributionSmokeCommand.php` | 44 |
| `Catalyst\Framework\Cli\Commands\DocsInventoryCommand` | `class` | `app/Framework/Cli/Commands/DocsInventoryCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\DocsSyncRuntimeCommand` | `class` | `app/Framework/Cli/Commands/DocsSyncRuntimeCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\DocumentsMvcRegressionCommand` | `class` | `app/Framework/Cli/Commands/DocumentsMvcRegressionCommand.php` | 43 |
| `Catalyst\Framework\Cli\Commands\ExportDevelopmentOverlayCommand` | `class` | `app/Framework/Cli/Commands/ExportDevelopmentOverlayCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\FeatureFlagsListCommand` | `class` | `app/Framework/Cli/Commands/FeatureFlagsListCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\FeatureFlagsSetCommand` | `class` | `app/Framework/Cli/Commands/FeatureFlagsSetCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\FixturesAuthCommand` | `class` | `app/Framework/Cli/Commands/FixturesAuthCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\GeoSmokeCommand` | `class` | `app/Framework/Cli/Commands/GeoSmokeCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\HelpCommand` | `class` | `app/Framework/Cli/Commands/HelpCommand.php` | 44 |
| `Catalyst\Framework\Cli\Commands\I18nInitLocaleCommand` | `class` | `app/Framework/Cli/Commands/I18nInitLocaleCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\I18nStatusCommand` | `class` | `app/Framework/Cli/Commands/I18nStatusCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\I18nSyncCommand` | `class` | `app/Framework/Cli/Commands/I18nSyncCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\I18nUsageLintCommand` | `class` | `app/Framework/Cli/Commands/I18nUsageLintCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\IdempotencySmokeCommand` | `class` | `app/Framework/Cli/Commands/IdempotencySmokeCommand.php` | 51 |
| `Catalyst\Framework\Cli\Commands\InspectHarnessCommand` | `class` | `app/Framework/Cli/Commands/InspectHarnessCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\InspectLintCommand` | `class` | `app/Framework/Cli/Commands/InspectLintCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\InspectModuleCommand` | `class` | `app/Framework/Cli/Commands/InspectModuleCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\InspectModulesCommand` | `class` | `app/Framework/Cli/Commands/InspectModulesCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\KeyGenerateCommand` | `class` | `app/Framework/Cli/Commands/KeyGenerateCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\MakeCommandCommand` | `class` | `app/Framework/Cli/Commands/MakeCommandCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakeControllerCommand` | `class` | `app/Framework/Cli/Commands/MakeControllerCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakeCrudCommand` | `class` | `app/Framework/Cli/Commands/MakeCrudCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakeMiddlewareCommand` | `class` | `app/Framework/Cli/Commands/MakeMiddlewareCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\MakeMigrationCommand` | `class` | `app/Framework/Cli/Commands/MakeMigrationCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakeModelCommand` | `class` | `app/Framework/Cli/Commands/MakeModelCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakeModuleCommand` | `class` | `app/Framework/Cli/Commands/MakeModuleCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakePolicyCommand` | `class` | `app/Framework/Cli/Commands/MakePolicyCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MakeRequestCommand` | `class` | `app/Framework/Cli/Commands/MakeRequestCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\MediaMvcRegressionCommand` | `class` | `app/Framework/Cli/Commands/MediaMvcRegressionCommand.php` | 43 |
| `Catalyst\Framework\Cli\Commands\MigrateCommand` | `class` | `app/Framework/Cli/Commands/MigrateCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\MigrateRollbackCommand` | `class` | `app/Framework/Cli/Commands/MigrateRollbackCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\MigrateStatusCommand` | `class` | `app/Framework/Cli/Commands/MigrateStatusCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\ModuleLocalizationRegressionCommand` | `class` | `app/Framework/Cli/Commands/ModuleLocalizationRegressionCommand.php` | 44 |
| `Catalyst\Framework\Cli\Commands\OrganizationSmokeCommand` | `class` | `app/Framework/Cli/Commands/OrganizationSmokeCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\PluginListCommand` | `class` | `app/Framework/Cli/Commands/PluginListCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\PluginToggleCommand` | `class` | `app/Framework/Cli/Commands/PluginToggleCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\PresenceSmokeCommand` | `class` | `app/Framework/Cli/Commands/PresenceSmokeCommand.php` | 48 |
| `Catalyst\Framework\Cli\Commands\QualityCheckCommand` | `class` | `app/Framework/Cli/Commands/QualityCheckCommand.php` | 43 |
| `Catalyst\Framework\Cli\Commands\QueueFailedCommand` | `class` | `app/Framework/Cli/Commands/QueueFailedCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\QueueRetryCommand` | `class` | `app/Framework/Cli/Commands/QueueRetryCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\QueueWorkCommand` | `class` | `app/Framework/Cli/Commands/QueueWorkCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ReferencesSmokeCommand` | `class` | `app/Framework/Cli/Commands/ReferencesSmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\ReportingRunCommand` | `class` | `app/Framework/Cli/Commands/ReportingRunCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ReportingSmokeCommand` | `class` | `app/Framework/Cli/Commands/ReportingSmokeCommand.php` | 53 |
| `Catalyst\Framework\Cli\Commands\ReportsContractSmokeCommand` | `class` | `app/Framework/Cli/Commands/ReportsContractSmokeCommand.php` | 53 |
| `Catalyst\Framework\Cli\Commands\RetentionRunCommand` | `class` | `app/Framework/Cli/Commands/RetentionRunCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\RetentionSmokeCommand` | `class` | `app/Framework/Cli/Commands/RetentionSmokeCommand.php` | 54 |
| `Catalyst\Framework\Cli\Commands\RolesMvcRegressionCommand` | `class` | `app/Framework/Cli/Commands/RolesMvcRegressionCommand.php` | 43 |
| `Catalyst\Framework\Cli\Commands\RouteBootstrapRegressionCommand` | `class` | `app/Framework/Cli/Commands/RouteBootstrapRegressionCommand.php` | 51 |
| `Catalyst\Framework\Cli\Commands\RouteCacheCommand` | `class` | `app/Framework/Cli/Commands/RouteCacheCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\RouteClearCommand` | `class` | `app/Framework/Cli/Commands/RouteClearCommand.php` | 44 |
| `Catalyst\Framework\Cli\Commands\RouteLintCommand` | `class` | `app/Framework/Cli/Commands/RouteLintCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\RouteListCommand` | `class` | `app/Framework/Cli/Commands/RouteListCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\ScaffoldAppSmokeCommand` | `class` | `app/Framework/Cli/Commands/ScaffoldAppSmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\ScheduleListCommand` | `class` | `app/Framework/Cli/Commands/ScheduleListCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\ScheduleRunCommand` | `class` | `app/Framework/Cli/Commands/ScheduleRunCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\SecurityCheckCommand` | `class` | `app/Framework/Cli/Commands/SecurityCheckCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\SecurityRegressionCommand` | `class` | `app/Framework/Cli/Commands/SecurityRegressionCommand.php` | 60 |
| `Catalyst\Framework\Cli\Commands\SensitivitySmokeCommand` | `class` | `app/Framework/Cli/Commands/SensitivitySmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\SequencesSmokeCommand` | `class` | `app/Framework/Cli/Commands/SequencesSmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\ShellNavigationSmokeCommand` | `class` | `app/Framework/Cli/Commands/ShellNavigationSmokeCommand.php` | 46 |
| `Catalyst\Framework\Cli\Commands\StatusCommand` | `class` | `app/Framework/Cli/Commands/StatusCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\StorageCleanCommand` | `class` | `app/Framework/Cli/Commands/StorageCleanCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\TemporalSmokeCommand` | `class` | `app/Framework/Cli/Commands/TemporalSmokeCommand.php` | 47 |
| `Catalyst\Framework\Cli\Commands\TenancySmokeCommand` | `class` | `app/Framework/Cli/Commands/TenancySmokeCommand.php` | 52 |
| `Catalyst\Framework\Cli\Commands\TenancyStatusCommand` | `class` | `app/Framework/Cli/Commands/TenancyStatusCommand.php` | 45 |
| `Catalyst\Framework\Cli\Commands\TimelineSmokeCommand` | `class` | `app/Framework/Cli/Commands/TimelineSmokeCommand.php` | 49 |
| `Catalyst\Framework\Cli\Commands\UpdateCheckCommand` | `class` | `app/Framework/Cli/Commands/UpdateCheckCommand.php` | 17 |
| `Catalyst\Framework\Cli\Commands\VersionCommand` | `class` | `app/Framework/Cli/Commands/VersionCommand.php` | 44 |
| `Catalyst\Framework\Cli\Commands\WorkflowSmokeCommand` | `class` | `app/Framework/Cli/Commands/WorkflowSmokeCommand.php` | 46 |
| `Catalyst\Framework\Cli\ScaffoldManager` | `class` | `app/Framework/Cli/ScaffoldManager.php` | 43 |
| `Catalyst\Framework\Cli\Support\PhpValueExporter` | `class` | `app/Framework/Cli/Support/PhpValueExporter.php` | 40 |
| `Catalyst\Framework\Cli\Support\RouteContractInspector` | `class` | `app/Framework/Cli/Support/RouteContractInspector.php` | 48 |
| `Catalyst\Framework\Cli\TerminalStyle` | `class` | `app/Framework/Cli/TerminalStyle.php` | 40 |
| `Catalyst\Framework\Concurrency\RecordClaimManager` | `class` | `app/Framework/Concurrency/RecordClaimManager.php` | 49 |
| `Catalyst\Framework\Concurrency\RecordClaimRepository` | `class` | `app/Framework/Concurrency/RecordClaimRepository.php` | 46 |
| `Catalyst\Framework\Config\LocalConfigManager` | `class` | `app/Framework/Config/LocalConfigManager.php` | 39 |
| `Catalyst\Framework\Container\Container` | `class` | `app/Framework/Container/Container.php` | 45 |
| `Catalyst\Framework\Controllers\CanonicalRedirectController` | `class` | `app/Framework/Controllers/CanonicalRedirectController.php` | 41 |
| `Catalyst\Framework\Controllers\Controller` | `class` | `app/Framework/Controllers/Controller.php` | 60 |
| `Catalyst\Framework\Controllers\FlashController` | `class` | `app/Framework/Controllers/FlashController.php` | 42 |
| `Catalyst\Framework\DataGrid\DataGrid` | `class` | `app/Framework/DataGrid/DataGrid.php` | 44 |
| `Catalyst\Framework\DataGrid\DataGridBulkActionNormalizer` | `class` | `app/Framework/DataGrid/DataGridBulkActionNormalizer.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridColumnNormalizer` | `class` | `app/Framework/DataGrid/DataGridColumnNormalizer.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridCsvExporter` | `class` | `app/Framework/DataGrid/DataGridCsvExporter.php` | 41 |
| `Catalyst\Framework\DataGrid\DataGridExportNormalizer` | `class` | `app/Framework/DataGrid/DataGridExportNormalizer.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridFilterNormalizer` | `class` | `app/Framework/DataGrid/DataGridFilterNormalizer.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridHtmlExportRenderer` | `class` | `app/Framework/DataGrid/DataGridHtmlExportRenderer.php` | 42 |
| `Catalyst\Framework\DataGrid\DataGridPaginationBuilder` | `class` | `app/Framework/DataGrid/DataGridPaginationBuilder.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridRowActionNormalizer` | `class` | `app/Framework/DataGrid/DataGridRowActionNormalizer.php` | 41 |
| `Catalyst\Framework\DataGrid\DataGridRowNormalizer` | `class` | `app/Framework/DataGrid/DataGridRowNormalizer.php` | 42 |
| `Catalyst\Framework\DataGrid\DataGridStateResolver` | `class` | `app/Framework/DataGrid/DataGridStateResolver.php` | 41 |
| `Catalyst\Framework\DataGrid\DataGridTextFormatter` | `class` | `app/Framework/DataGrid/DataGridTextFormatter.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridUrlBuilder` | `class` | `app/Framework/DataGrid/DataGridUrlBuilder.php` | 39 |
| `Catalyst\Framework\DataGrid\DataGridViewModel` | `class` | `app/Framework/DataGrid/DataGridViewModel.php` | 36 |
| `Catalyst\Framework\Database\Collection` | `class` | `app/Framework/Database/Collection.php` | 53 |
| `Catalyst\Framework\Database\Concerns\HasModelAttributes` | `trait` | `app/Framework/Database/Concerns/HasModelAttributes.php` | 43 |
| `Catalyst\Framework\Database\Concerns\HasModelLifecycleHooks` | `trait` | `app/Framework/Database/Concerns/HasModelLifecycleHooks.php` | 41 |
| `Catalyst\Framework\Database\Concerns\HasModelRelationships` | `trait` | `app/Framework/Database/Concerns/HasModelRelationships.php` | 46 |
| `Catalyst\Framework\Database\Concerns\PersistsModelState` | `trait` | `app/Framework/Database/Concerns/PersistsModelState.php` | 44 |
| `Catalyst\Framework\Database\Connection` | `class` | `app/Framework/Database/Connection.php` | 51 |
| `Catalyst\Framework\Database\DatabaseManager` | `class` | `app/Framework/Database/DatabaseManager.php` | 79 |
| `Catalyst\Framework\Database\Migration` | `class` | `app/Framework/Database/Migration.php` | 41 |
| `Catalyst\Framework\Database\MigrationRunner` | `class` | `app/Framework/Database/MigrationRunner.php` | 42 |
| `Catalyst\Framework\Database\Model` | `class` | `app/Framework/Database/Model.php` | 98 |
| `Catalyst\Framework\Database\ModelQueryBuilder` | `class` | `app/Framework/Database/ModelQueryBuilder.php` | 52 |
| `Catalyst\Framework\Database\Pagination` | `class` | `app/Framework/Database/Pagination.php` | 42 |
| `Catalyst\Framework\Database\PdoOptionsFactory` | `class` | `app/Framework/Database/PdoOptionsFactory.php` | 46 |
| `Catalyst\Framework\Database\QueryBuilder` | `class` | `app/Framework/Database/QueryBuilder.php` | 44 |
| `Catalyst\Framework\Database\Relations\BelongsTo` | `class` | `app/Framework/Database/Relations/BelongsTo.php` | 41 |
| `Catalyst\Framework\Database\Relations\BelongsToMany` | `class` | `app/Framework/Database/Relations/BelongsToMany.php` | 42 |
| `Catalyst\Framework\Database\Relations\HasMany` | `class` | `app/Framework/Database/Relations/HasMany.php` | 42 |
| `Catalyst\Framework\Database\Relations\HasOne` | `class` | `app/Framework/Database/Relations/HasOne.php` | 41 |
| `Catalyst\Framework\Database\Relations\Relation` | `class` | `app/Framework/Database/Relations/Relation.php` | 44 |
| `Catalyst\Framework\Database\SqlReference` | `class` | `app/Framework/Database/SqlReference.php` | 41 |
| `Catalyst\Framework\Database\Transaction` | `class` | `app/Framework/Database/Transaction.php` | 47 |
| `Catalyst\Framework\Deletion\ReverseCascadeDeletePlan` | `class` | `app/Framework/Deletion/ReverseCascadeDeletePlan.php` | 39 |
| `Catalyst\Framework\Deletion\ReverseCascadeDeleteResult` | `class` | `app/Framework/Deletion/ReverseCascadeDeleteResult.php` | 39 |
| `Catalyst\Framework\Deletion\ReverseCascadeDeleteService` | `class` | `app/Framework/Deletion/ReverseCascadeDeleteService.php` | 42 |
| `Catalyst\Framework\Deletion\ReverseCascadeDeleteStep` | `class` | `app/Framework/Deletion/ReverseCascadeDeleteStep.php` | 39 |
| `Catalyst\Framework\Deployment\DeploymentManager` | `class` | `app/Framework/Deployment/DeploymentManager.php` | 50 |
| `Catalyst\Framework\Deployment\DeploymentRunRepository` | `class` | `app/Framework/Deployment/DeploymentRunRepository.php` | 44 |
| `Catalyst\Framework\Document\DocumentTemplateManager` | `class` | `app/Framework/Document/DocumentTemplateManager.php` | 51 |
| `Catalyst\Framework\Document\DocumentTemplateRepository` | `class` | `app/Framework/Document/DocumentTemplateRepository.php` | 47 |
| `Catalyst\Framework\Document\Pdf\PdfRendererInterface` | `interface` | `app/Framework/Document/Pdf/PdfRendererInterface.php` | 39 |
| `Catalyst\Framework\Document\Pdf\SimplePdfWriter` | `class` | `app/Framework/Document/Pdf/SimplePdfWriter.php` | 39 |
| `Catalyst\Framework\Document\TemplateStringRenderer` | `class` | `app/Framework/Document/TemplateStringRenderer.php` | 39 |
| `Catalyst\Framework\Documentation\RuntimeInventoryGenerator` | `class` | `app/Framework/Documentation/RuntimeInventoryGenerator.php` | 43 |
| `Catalyst\Framework\Enums\AppEnvironment` | `enum` | `app/Framework/Enums/AppEnvironment.php` | 48 |
| `Catalyst\Framework\Event\EventBus` | `class` | `app/Framework/Event/EventBus.php` | 47 |
| `Catalyst\Framework\Event\EventListenerDefinition` | `class` | `app/Framework/Event/EventListenerDefinition.php` | 39 |
| `Catalyst\Framework\Event\EventListenerInterface` | `interface` | `app/Framework/Event/EventListenerInterface.php` | 41 |
| `Catalyst\Framework\Event\FrameworkEventCatalog` | `class` | `app/Framework/Event/FrameworkEventCatalog.php` | 44 |
| `Catalyst\Framework\Event\Listeners\CaptureAuditEventListener` | `class` | `app/Framework/Event/Listeners/CaptureAuditEventListener.php` | 43 |
| `Catalyst\Framework\Event\Listeners\CaptureTimelineMilestoneListener` | `class` | `app/Framework/Event/Listeners/CaptureTimelineMilestoneListener.php` | 43 |
| `Catalyst\Framework\Event\Listeners\DeliverNotificationListener` | `class` | `app/Framework/Event/Listeners/DeliverNotificationListener.php` | 44 |
| `Catalyst\Framework\Event\Listeners\ProcessAutomationEventListener` | `class` | `app/Framework/Event/Listeners/ProcessAutomationEventListener.php` | 43 |
| `Catalyst\Framework\FeatureFlag\FeatureFlagManager` | `class` | `app/Framework/FeatureFlag/FeatureFlagManager.php` | 53 |
| `Catalyst\Framework\FeatureFlag\FeatureFlagOverrideRepository` | `class` | `app/Framework/FeatureFlag/FeatureFlagOverrideRepository.php` | 47 |
| `Catalyst\Framework\Form\FormBuilder` | `class` | `app/Framework/Form/FormBuilder.php` | 39 |
| `Catalyst\Framework\Form\FormBuilderViewModel` | `class` | `app/Framework/Form/FormBuilderViewModel.php` | 36 |
| `Catalyst\Framework\Geo\BoundingBox` | `class` | `app/Framework/Geo/BoundingBox.php` | 41 |
| `Catalyst\Framework\Geo\Coordinate` | `class` | `app/Framework/Geo/Coordinate.php` | 41 |
| `Catalyst\Framework\Geo\GeoManager` | `class` | `app/Framework/Geo/GeoManager.php` | 41 |
| `Catalyst\Framework\Health\HealthReportBuilder` | `class` | `app/Framework/Health/HealthReportBuilder.php` | 57 |
| `Catalyst\Framework\Http\ApiRequest` | `class` | `app/Framework/Http/ApiRequest.php` | 39 |
| `Catalyst\Framework\Http\ErrorResponseFactory` | `class` | `app/Framework/Http/ErrorResponseFactory.php` | 42 |
| `Catalyst\Framework\Http\FileValidator` | `class` | `app/Framework/Http/FileValidator.php` | 39 |
| `Catalyst\Framework\Http\FormRequest` | `class` | `app/Framework/Http/FormRequest.php` | 44 |
| `Catalyst\Framework\Http\HtmlResponse` | `class` | `app/Framework/Http/HtmlResponse.php` | 39 |
| `Catalyst\Framework\Http\JsonResponse` | `class` | `app/Framework/Http/JsonResponse.php` | 44 |
| `Catalyst\Framework\Http\RedirectResponse` | `class` | `app/Framework/Http/RedirectResponse.php` | 41 |
| `Catalyst\Framework\Http\RedirectTarget` | `class` | `app/Framework/Http/RedirectTarget.php` | 39 |
| `Catalyst\Framework\Http\Request` | `class` | `app/Framework/Http/Request.php` | 43 |
| `Catalyst\Framework\Http\Response` | `class` | `app/Framework/Http/Response.php` | 41 |
| `Catalyst\Framework\Http\UploadedFile` | `class` | `app/Framework/Http/UploadedFile.php` | 43 |
| `Catalyst\Framework\Idempotency\IdempotencyConflictException` | `class` | `app/Framework/Idempotency/IdempotencyConflictException.php` | 41 |
| `Catalyst\Framework\Idempotency\IdempotencyInProgressException` | `class` | `app/Framework/Idempotency/IdempotencyInProgressException.php` | 41 |
| `Catalyst\Framework\Idempotency\IdempotencyManager` | `class` | `app/Framework/Idempotency/IdempotencyManager.php` | 42 |
| `Catalyst\Framework\Idempotency\IdempotencyRepository` | `class` | `app/Framework/Idempotency/IdempotencyRepository.php` | 43 |
| `Catalyst\Framework\Localization\AtomicLocaleCatalogWriter` | `class` | `app/Framework/Localization/AtomicLocaleCatalogWriter.php` | 13 |
| `Catalyst\Framework\Localization\LocalizationManager` | `class` | `app/Framework/Localization/LocalizationManager.php` | 45 |
| `Catalyst\Framework\Mail\DkimGenerator` | `class` | `app/Framework/Mail/DkimGenerator.php` | 45 |
| `Catalyst\Framework\Mail\MailAttachment` | `class` | `app/Framework/Mail/MailAttachment.php` | 42 |
| `Catalyst\Framework\Mail\MailManager` | `class` | `app/Framework/Mail/MailManager.php` | 55 |
| `Catalyst\Framework\Mail\MailMessage` | `class` | `app/Framework/Mail/MailMessage.php` | 45 |
| `Catalyst\Framework\Mail\MailTemplate` | `class` | `app/Framework/Mail/MailTemplate.php` | 46 |
| `Catalyst\Framework\Media\MediaManager` | `class` | `app/Framework/Media/MediaManager.php` | 48 |
| `Catalyst\Framework\Media\MediaRepository` | `class` | `app/Framework/Media/MediaRepository.php` | 47 |
| `Catalyst\Framework\Metadata\MetadataFieldRepository` | `class` | `app/Framework/Metadata/MetadataFieldRepository.php` | 47 |
| `Catalyst\Framework\Metadata\MetadataManager` | `class` | `app/Framework/Metadata/MetadataManager.php` | 45 |
| `Catalyst\Framework\Metadata\MetadataResourceRegistry` | `class` | `app/Framework/Metadata/MetadataResourceRegistry.php` | 41 |
| `Catalyst\Framework\Metadata\MetadataValueRepository` | `class` | `app/Framework/Metadata/MetadataValueRepository.php` | 48 |
| `Catalyst\Framework\Middleware\ApiTokenMiddleware` | `class` | `app/Framework/Middleware/ApiTokenMiddleware.php` | 46 |
| `Catalyst\Framework\Middleware\AuthMiddleware` | `class` | `app/Framework/Middleware/AuthMiddleware.php` | 60 |
| `Catalyst\Framework\Middleware\BasicAuthMiddleware` | `class` | `app/Framework/Middleware/BasicAuthMiddleware.php` | 52 |
| `Catalyst\Framework\Middleware\CallableMiddleware` | `class` | `app/Framework/Middleware/CallableMiddleware.php` | 45 |
| `Catalyst\Framework\Middleware\CanonicalPathRedirectMiddleware` | `class` | `app/Framework/Middleware/CanonicalPathRedirectMiddleware.php` | 45 |
| `Catalyst\Framework\Middleware\CoreMiddleware` | `class` | `app/Framework/Middleware/CoreMiddleware.php` | 49 |
| `Catalyst\Framework\Middleware\CorsMiddleware` | `class` | `app/Framework/Middleware/CorsMiddleware.php` | 64 |
| `Catalyst\Framework\Middleware\CsrfMiddleware` | `class` | `app/Framework/Middleware/CsrfMiddleware.php` | 55 |
| `Catalyst\Framework\Middleware\DebugMiddleware` | `class` | `app/Framework/Middleware/DebugMiddleware.php` | 52 |
| `Catalyst\Framework\Middleware\DevToolsGuardMiddleware` | `class` | `app/Framework/Middleware/DevToolsGuardMiddleware.php` | 57 |
| `Catalyst\Framework\Middleware\FeatureFlagInterface` | `interface` | `app/Framework/Middleware/FeatureFlagInterface.php` | 43 |
| `Catalyst\Framework\Middleware\GuestMiddleware` | `class` | `app/Framework/Middleware/GuestMiddleware.php` | 54 |
| `Catalyst\Framework\Middleware\LoginThrottleMiddleware` | `class` | `app/Framework/Middleware/LoginThrottleMiddleware.php` | 55 |
| `Catalyst\Framework\Middleware\MiddlewareInterface` | `interface` | `app/Framework/Middleware/MiddlewareInterface.php` | 46 |
| `Catalyst\Framework\Middleware\MiddlewareStack` | `class` | `app/Framework/Middleware/MiddlewareStack.php` | 46 |
| `Catalyst\Framework\Middleware\RequestThrottlingMiddleware` | `class` | `app/Framework/Middleware/RequestThrottlingMiddleware.php` | 48 |
| `Catalyst\Framework\Middleware\RoleMiddleware` | `class` | `app/Framework/Middleware/RoleMiddleware.php` | 75 |
| `Catalyst\Framework\Middleware\RouteFeatureMiddleware` | `class` | `app/Framework/Middleware/RouteFeatureMiddleware.php` | 47 |
| `Catalyst\Framework\Middleware\SecurityHeadersMiddleware` | `class` | `app/Framework/Middleware/SecurityHeadersMiddleware.php` | 51 |
| `Catalyst\Framework\Middleware\SetupAccessTrait` | `trait` | `app/Framework/Middleware/SetupAccessTrait.php` | 42 |
| `Catalyst\Framework\Middleware\SetupGuardMiddleware` | `class` | `app/Framework/Middleware/SetupGuardMiddleware.php` | 62 |
| `Catalyst\Framework\Middleware\SetupMiddleware` | `class` | `app/Framework/Middleware/SetupMiddleware.php` | 60 |
| `Catalyst\Framework\Middleware\TenancyContextMiddleware` | `class` | `app/Framework/Middleware/TenancyContextMiddleware.php` | 45 |
| `Catalyst\Framework\Middleware\ThrottleProfileCatalog` | `class` | `app/Framework/Middleware/ThrottleProfileCatalog.php` | 41 |
| `Catalyst\Framework\Middleware\WebSocketBootMiddleware` | `class` | `app/Framework/Middleware/WebSocketBootMiddleware.php` | 53 |
| `Catalyst\Framework\Module\AppBoundaryLinter` | `class` | `app/Framework/Module/AppBoundaryLinter.php` | 39 |
| `Catalyst\Framework\Module\BuiltInModuleDeclarations` | `class` | `app/Framework/Module/BuiltInModuleDeclarations.php` | 39 |
| `Catalyst\Framework\Module\ModuleAssetPublisher` | `class` | `app/Framework/Module/ModuleAssetPublisher.php` | 42 |
| `Catalyst\Framework\Module\ModuleBlueprintFactory` | `class` | `app/Framework/Module/ModuleBlueprintFactory.php` | 43 |
| `Catalyst\Framework\Module\ModuleDiscovery` | `class` | `app/Framework/Module/ModuleDiscovery.php` | 41 |
| `Catalyst\Framework\Module\ModuleFileFactory` | `class` | `app/Framework/Module/ModuleFileFactory.php` | 42 |
| `Catalyst\Framework\Module\ModuleHarnessInspector` | `class` | `app/Framework/Module/ModuleHarnessInspector.php` | 47 |
| `Catalyst\Framework\Module\ModuleInspector` | `class` | `app/Framework/Module/ModuleInspector.php` | 49 |
| `Catalyst\Framework\Module\ModuleLinter` | `class` | `app/Framework/Module/ModuleLinter.php` | 46 |
| `Catalyst\Framework\Module\ModuleLocalizationDecorator` | `class` | `app/Framework/Module/ModuleLocalizationDecorator.php` | 39 |
| `Catalyst\Framework\Module\ModuleManifestBuilder` | `class` | `app/Framework/Module/ModuleManifestBuilder.php` | 41 |
| `Catalyst\Framework\Module\ModuleManifestLoader` | `class` | `app/Framework/Module/ModuleManifestLoader.php` | 41 |
| `Catalyst\Framework\Module\ModuleRegistry` | `class` | `app/Framework/Module/ModuleRegistry.php` | 42 |
| `Catalyst\Framework\Module\ModuleRouteOwnershipResolver` | `class` | `app/Framework/Module/ModuleRouteOwnershipResolver.php` | 42 |
| `Catalyst\Framework\Module\ModuleRuntimeDocsGenerator` | `class` | `app/Framework/Module/ModuleRuntimeDocsGenerator.php` | 39 |
| `Catalyst\Framework\Module\ModuleRuntimeStateDecorator` | `class` | `app/Framework/Module/ModuleRuntimeStateDecorator.php` | 43 |
| `Catalyst\Framework\Module\ModuleScaffoldService` | `class` | `app/Framework/Module/ModuleScaffoldService.php` | 42 |
| `Catalyst\Framework\Navigation\ApplicationNavigationProvider` | `class` | `app/Framework/Navigation/ApplicationNavigationProvider.php` | 12 |
| `Catalyst\Framework\Navigation\DemoUiNavigationProvider` | `class` | `app/Framework/Navigation/DemoUiNavigationProvider.php` | 12 |
| `Catalyst\Framework\Navigation\FrameworkNavigationProvider` | `class` | `app/Framework/Navigation/FrameworkNavigationProvider.php` | 12 |
| `Catalyst\Framework\Navigation\NavigationModelProvider` | `interface` | `app/Framework/Navigation/NavigationModelProvider.php` | 12 |
| `Catalyst\Framework\Navigation\NavigationModelSelector` | `class` | `app/Framework/Navigation/NavigationModelSelector.php` | 14 |
| `Catalyst\Framework\Navigation\NavigationRegistry` | `class` | `app/Framework/Navigation/NavigationRegistry.php` | 45 |
| `Catalyst\Framework\Navigation\NavigationTreeNormalizer` | `class` | `app/Framework/Navigation/NavigationTreeNormalizer.php` | 14 |
| `Catalyst\Framework\Navigation\ShellNavigationPresenter` | `class` | `app/Framework/Navigation/ShellNavigationPresenter.php` | 41 |
| `Catalyst\Framework\Notification\Notification` | `class` | `app/Framework/Notification/Notification.php` | 45 |
| `Catalyst\Framework\Notification\NotificationBag` | `class` | `app/Framework/Notification/NotificationBag.php` | 42 |
| `Catalyst\Framework\Notification\NotificationManager` | `class` | `app/Framework/Notification/NotificationManager.php` | 57 |
| `Catalyst\Framework\Notification\NotificationPosition` | `enum` | `app/Framework/Notification/NotificationPosition.php` | 41 |
| `Catalyst\Framework\Notification\NotificationRepository` | `class` | `app/Framework/Notification/NotificationRepository.php` | 45 |
| `Catalyst\Framework\Notification\NotificationType` | `enum` | `app/Framework/Notification/NotificationType.php` | 42 |
| `Catalyst\Framework\Organization\OrganizationClassification` | `class` | `app/Framework/Organization/OrganizationClassification.php` | 15 |
| `Catalyst\Framework\Organization\OrganizationClassificationPresenter` | `class` | `app/Framework/Organization/OrganizationClassificationPresenter.php` | 13 |
| `Catalyst\Framework\Organization\OrganizationRepository` | `class` | `app/Framework/Organization/OrganizationRepository.php` | 19 |
| `Catalyst\Framework\Plugin\PluginManager` | `class` | `app/Framework/Plugin/PluginManager.php` | 48 |
| `Catalyst\Framework\Plugin\PluginRegistry` | `class` | `app/Framework/Plugin/PluginRegistry.php` | 41 |
| `Catalyst\Framework\Presence\RecordPresenceManager` | `class` | `app/Framework/Presence/RecordPresenceManager.php` | 43 |
| `Catalyst\Framework\Presence\RecordPresenceViewModel` | `class` | `app/Framework/Presence/RecordPresenceViewModel.php` | 33 |
| `Catalyst\Framework\Queue\Jobs\DispatchNotificationJob` | `class` | `app/Framework/Queue/Jobs/DispatchNotificationJob.php` | 43 |
| `Catalyst\Framework\Queue\Jobs\InvokeQueuedListenerJob` | `class` | `app/Framework/Queue/Jobs/InvokeQueuedListenerJob.php` | 44 |
| `Catalyst\Framework\Queue\Jobs\PruneQueueHistoryJob` | `class` | `app/Framework/Queue/Jobs/PruneQueueHistoryJob.php` | 43 |
| `Catalyst\Framework\Queue\QueueJobSerializer` | `class` | `app/Framework/Queue/QueueJobSerializer.php` | 41 |
| `Catalyst\Framework\Queue\QueueManager` | `class` | `app/Framework/Queue/QueueManager.php` | 44 |
| `Catalyst\Framework\Queue\QueueRepository` | `class` | `app/Framework/Queue/QueueRepository.php` | 46 |
| `Catalyst\Framework\Queue\QueueSchemaManager` | `class` | `app/Framework/Queue/QueueSchemaManager.php` | 42 |
| `Catalyst\Framework\Queue\QueueSettings` | `class` | `app/Framework/Queue/QueueSettings.php` | 41 |
| `Catalyst\Framework\Queue\QueueWorker` | `class` | `app/Framework/Queue/QueueWorker.php` | 42 |
| `Catalyst\Framework\Queue\QueueableJobInterface` | `interface` | `app/Framework/Queue/QueueableJobInterface.php` | 39 |
| `Catalyst\Framework\Reference\EntityReference` | `class` | `app/Framework/Reference/EntityReference.php` | 41 |
| `Catalyst\Framework\Reference\EntityReferenceRegistry` | `class` | `app/Framework/Reference/EntityReferenceRegistry.php` | 41 |
| `Catalyst\Framework\Release\ReleaseMetadata` | `class` | `app/Framework/Release/ReleaseMetadata.php` | 10 |
| `Catalyst\Framework\Reporting\AttachmentReportProvider` | `class` | `app/Framework/Reporting/AttachmentReportProvider.php` | 43 |
| `Catalyst\Framework\Reporting\DataGridReportExporter` | `class` | `app/Framework/Reporting/DataGridReportExporter.php` | 42 |
| `Catalyst\Framework\Reporting\Jobs\RunReportJob` | `class` | `app/Framework/Reporting/Jobs/RunReportJob.php` | 42 |
| `Catalyst\Framework\Reporting\ReportDefinition` | `class` | `app/Framework/Reporting/ReportDefinition.php` | 41 |
| `Catalyst\Framework\Reporting\ReportExportResult` | `class` | `app/Framework/Reporting/ReportExportResult.php` | 39 |
| `Catalyst\Framework\Reporting\ReportExporterInterface` | `interface` | `app/Framework/Reporting/ReportExporterInterface.php` | 39 |
| `Catalyst\Framework\Reporting\ReportFormat` | `class` | `app/Framework/Reporting/ReportFormat.php` | 41 |
| `Catalyst\Framework\Reporting\ReportProviderInterface` | `interface` | `app/Framework/Reporting/ReportProviderInterface.php` | 39 |
| `Catalyst\Framework\Reporting\ReportProviderRegistry` | `class` | `app/Framework/Reporting/ReportProviderRegistry.php` | 41 |
| `Catalyst\Framework\Reporting\ReportingManager` | `class` | `app/Framework/Reporting/ReportingManager.php` | 49 |
| `Catalyst\Framework\Reporting\SimplePdfReportExporter` | `class` | `app/Framework/Reporting/SimplePdfReportExporter.php` | 42 |
| `Catalyst\Framework\Retention\Jobs\RunRetentionPoliciesJob` | `class` | `app/Framework/Retention/Jobs/RunRetentionPoliciesJob.php` | 42 |
| `Catalyst\Framework\Retention\RetentionManager` | `class` | `app/Framework/Retention/RetentionManager.php` | 50 |
| `Catalyst\Framework\Route\CanonicalPathRedirector` | `class` | `app/Framework/Route/CanonicalPathRedirector.php` | 39 |
| `Catalyst\Framework\Route\GlobalMiddlewareRegistrar` | `class` | `app/Framework/Route/GlobalMiddlewareRegistrar.php` | 47 |
| `Catalyst\Framework\Route\Route` | `class` | `app/Framework/Route/Route.php` | 45 |
| `Catalyst\Framework\Route\RouteCollection` | `class` | `app/Framework/Route/RouteCollection.php` | 48 |
| `Catalyst\Framework\Route\RouteCompiler` | `class` | `app/Framework/Route/RouteCompiler.php` | 42 |
| `Catalyst\Framework\Route\RouteDispatcher` | `class` | `app/Framework/Route/RouteDispatcher.php` | 58 |
| `Catalyst\Framework\Route\RouteGroup` | `class` | `app/Framework/Route/RouteGroup.php` | 42 |
| `Catalyst\Framework\Route\Router` | `class` | `app/Framework/Route/Router.php` | 53 |
| `Catalyst\Framework\Route\UrlGenerator` | `class` | `app/Framework/Route/UrlGenerator.php` | 44 |
| `Catalyst\Framework\Scaffolding\Crud\CrudAssetPublisher` | `class` | `app/Framework/Scaffolding/Crud/CrudAssetPublisher.php` | 42 |
| `Catalyst\Framework\Scaffolding\Crud\CrudBlueprintFactory` | `class` | `app/Framework/Scaffolding/Crud/CrudBlueprintFactory.php` | 45 |
| `Catalyst\Framework\Scaffolding\Crud\CrudFieldDefinitionParser` | `class` | `app/Framework/Scaffolding/Crud/CrudFieldDefinitionParser.php` | 41 |
| `Catalyst\Framework\Scaffolding\Crud\CrudFileFactory` | `class` | `app/Framework/Scaffolding/Crud/CrudFileFactory.php` | 42 |
| `Catalyst\Framework\Scaffolding\Crud\CrudScaffoldService` | `class` | `app/Framework/Scaffolding/Crud/CrudScaffoldService.php` | 42 |
| `Catalyst\Framework\Scaffolding\Crud\CrudSchemaBuilder` | `class` | `app/Framework/Scaffolding/Crud/CrudSchemaBuilder.php` | 39 |
| `Catalyst\Framework\Schedule\CronExpression` | `class` | `app/Framework/Schedule/CronExpression.php` | 41 |
| `Catalyst\Framework\Schedule\FrameworkScheduleCatalog` | `class` | `app/Framework/Schedule/FrameworkScheduleCatalog.php` | 44 |
| `Catalyst\Framework\Schedule\ScheduleLockManager` | `class` | `app/Framework/Schedule/ScheduleLockManager.php` | 42 |
| `Catalyst\Framework\Schedule\ScheduleRegistry` | `class` | `app/Framework/Schedule/ScheduleRegistry.php` | 42 |
| `Catalyst\Framework\Schedule\ScheduleRepository` | `class` | `app/Framework/Schedule/ScheduleRepository.php` | 45 |
| `Catalyst\Framework\Schedule\ScheduleRunner` | `class` | `app/Framework/Schedule/ScheduleRunner.php` | 47 |
| `Catalyst\Framework\Schedule\ScheduleSchemaManager` | `class` | `app/Framework/Schedule/ScheduleSchemaManager.php` | 41 |
| `Catalyst\Framework\Schedule\ScheduleSettings` | `class` | `app/Framework/Schedule/ScheduleSettings.php` | 41 |
| `Catalyst\Framework\Security\SignedSerializedPayload` | `class` | `app/Framework/Security/SignedSerializedPayload.php` | 41 |
| `Catalyst\Framework\Sensitivity\DataClassificationRegistry` | `class` | `app/Framework/Sensitivity/DataClassificationRegistry.php` | 41 |
| `Catalyst\Framework\Sensitivity\SensitiveDataPolicy` | `class` | `app/Framework/Sensitivity/SensitiveDataPolicy.php` | 42 |
| `Catalyst\Framework\Sequence\DatabaseSequenceStore` | `class` | `app/Framework/Sequence/DatabaseSequenceStore.php` | 42 |
| `Catalyst\Framework\Sequence\InMemorySequenceStore` | `class` | `app/Framework/Sequence/InMemorySequenceStore.php` | 39 |
| `Catalyst\Framework\Sequence\SequenceManager` | `class` | `app/Framework/Sequence/SequenceManager.php` | 42 |
| `Catalyst\Framework\Sequence\SequenceStoreInterface` | `interface` | `app/Framework/Sequence/SequenceStoreInterface.php` | 39 |
| `Catalyst\Framework\Session\DatabaseSessionHandler` | `class` | `app/Framework/Session/DatabaseSessionHandler.php` | 45 |
| `Catalyst\Framework\Session\FlashBag` | `class` | `app/Framework/Session/FlashBag.php` | 43 |
| `Catalyst\Framework\Session\FlashMessage` | `class` | `app/Framework/Session/FlashMessage.php` | 44 |
| `Catalyst\Framework\Session\SessionManager` | `class` | `app/Framework/Session/SessionManager.php` | 47 |
| `Catalyst\Framework\Session\ToastQueue` | `class` | `app/Framework/Session/ToastQueue.php` | 52 |
| `Catalyst\Framework\Storage\FtpStorageAdapter` | `class` | `app/Framework/Storage/FtpStorageAdapter.php` | 42 |
| `Catalyst\Framework\Storage\LocalStorageAdapter` | `class` | `app/Framework/Storage/LocalStorageAdapter.php` | 42 |
| `Catalyst\Framework\Storage\StorageAdapterInterface` | `interface` | `app/Framework/Storage/StorageAdapterInterface.php` | 41 |
| `Catalyst\Framework\Storage\StorageManager` | `class` | `app/Framework/Storage/StorageManager.php` | 44 |
| `Catalyst\Framework\Temporal\EffectiveWindow` | `class` | `app/Framework/Temporal/EffectiveWindow.php` | 43 |
| `Catalyst\Framework\Tenancy\TenancyManager` | `class` | `app/Framework/Tenancy/TenancyManager.php` | 44 |
| `Catalyst\Framework\Testing\AuthFixtureCatalog` | `class` | `app/Framework/Testing/AuthFixtureCatalog.php` | 39 |
| `Catalyst\Framework\Testing\AuthFixtureFactory` | `class` | `app/Framework/Testing/AuthFixtureFactory.php` | 42 |
| `Catalyst\Framework\Testing\AuthFixtureManager` | `class` | `app/Framework/Testing/AuthFixtureManager.php` | 51 |
| `Catalyst\Framework\Timeline\TimelineManager` | `class` | `app/Framework/Timeline/TimelineManager.php` | 45 |
| `Catalyst\Framework\Timeline\TimelineRepository` | `class` | `app/Framework/Timeline/TimelineRepository.php` | 46 |
| `Catalyst\Framework\Traits\BelongsToTenantTrait` | `trait` | `app/Framework/Traits/BelongsToTenantTrait.php` | 43 |
| `Catalyst\Framework\Traits\ErrorTypeTrait` | `trait` | `app/Framework/Traits/ErrorTypeTrait.php` | 42 |
| `Catalyst\Framework\Traits\FrontResourceTrait` | `trait` | `app/Framework/Traits/FrontResourceTrait.php` | 71 |
| `Catalyst\Framework\Traits\HandlesFormEventsTrait` | `trait` | `app/Framework/Traits/HandlesFormEventsTrait.php` | 77 |
| `Catalyst\Framework\Traits\HasAuditLogTrait` | `trait` | `app/Framework/Traits/HasAuditLogTrait.php` | 85 |
| `Catalyst\Framework\Traits\HasOptimisticLockingTrait` | `trait` | `app/Framework/Traits/HasOptimisticLockingTrait.php` | 41 |
| `Catalyst\Framework\Traits\HasSoftDeletesTrait` | `trait` | `app/Framework/Traits/HasSoftDeletesTrait.php` | 66 |
| `Catalyst\Framework\Traits\HasTimestampsTrait` | `trait` | `app/Framework/Traits/HasTimestampsTrait.php` | 62 |
| `Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait` | `trait` | `app/Framework/Traits/InteractsWithRecordClaimsTrait.php` | 44 |
| `Catalyst\Framework\Traits\LoadsFeatureConfigTrait` | `trait` | `app/Framework/Traits/LoadsFeatureConfigTrait.php` | 60 |
| `Catalyst\Framework\Traits\OutputCleanerTrait` | `trait` | `app/Framework/Traits/OutputCleanerTrait.php` | 39 |
| `Catalyst\Framework\Traits\SingletonTrait` | `trait` | `app/Framework/Traits/SingletonTrait.php` | 42 |
| `Catalyst\Framework\Versioning\VersionManager` | `class` | `app/Framework/Versioning/VersionManager.php` | 48 |
| `Catalyst\Framework\Versioning\VersionRepository` | `class` | `app/Framework/Versioning/VersionRepository.php` | 46 |
| `Catalyst\Framework\View\AssetUrl` | `class` | `app/Framework/View/AssetUrl.php` | 12 |
| `Catalyst\Framework\View\DocumentScope` | `class` | `app/Framework/View/DocumentScope.php` | 24 |
| `Catalyst\Framework\View\HtmlAllowlistSanitizer` | `class` | `app/Framework/View/HtmlAllowlistSanitizer.php` | 43 |
| `Catalyst\Framework\View\InlineJson` | `class` | `app/Framework/View/InlineJson.php` | 41 |
| `Catalyst\Framework\View\ModuleViewPathRegistrar` | `class` | `app/Framework/View/ModuleViewPathRegistrar.php` | 39 |
| `Catalyst\Framework\View\PageHeaderViewModel` | `class` | `app/Framework/View/PageHeaderViewModel.php` | 10 |
| `Catalyst\Framework\View\StatusBarViewModel` | `class` | `app/Framework/View/StatusBarViewModel.php` | 24 |
| `Catalyst\Framework\View\TopbarViewModel` | `class` | `app/Framework/View/TopbarViewModel.php` | 15 |
| `Catalyst\Framework\View\TrustedHtml` | `class` | `app/Framework/View/TrustedHtml.php` | 39 |
| `Catalyst\Framework\View\View` | `class` | `app/Framework/View/View.php` | 46 |
| `Catalyst\Framework\View\ViewTokenRenderer` | `class` | `app/Framework/View/ViewTokenRenderer.php` | 43 |
| `Catalyst\Framework\WebSocket\WebSocketPublisher` | `class` | `app/Framework/WebSocket/WebSocketPublisher.php` | 49 |
| `Catalyst\Framework\WebSocket\WebSocketServer` | `class` | `app/Framework/WebSocket/WebSocketServer.php` | 52 |
| `Catalyst\Framework\WebSocket\WebSocketToken` | `class` | `app/Framework/WebSocket/WebSocketToken.php` | 49 |
| `Catalyst\Framework\Workflow\FrameworkWorkflowCatalog` | `class` | `app/Framework/Workflow/FrameworkWorkflowCatalog.php` | 41 |
| `Catalyst\Framework\Workflow\WorkflowDefinition` | `class` | `app/Framework/Workflow/WorkflowDefinition.php` | 39 |
| `Catalyst\Framework\Workflow\WorkflowDefinitionRegistry` | `class` | `app/Framework/Workflow/WorkflowDefinitionRegistry.php` | 41 |
| `Catalyst\Framework\Workflow\WorkflowManager` | `class` | `app/Framework/Workflow/WorkflowManager.php` | 51 |
| `Catalyst\Framework\Workflow\WorkflowRepository` | `class` | `app/Framework/Workflow/WorkflowRepository.php` | 47 |
| `Catalyst\Framework\Workflow\WorkflowTransitionDecision` | `class` | `app/Framework/Workflow/WorkflowTransitionDecision.php` | 39 |
| `Catalyst\Framework\Workflow\WorkflowTransitionEvaluator` | `class` | `app/Framework/Workflow/WorkflowTransitionEvaluator.php` | 39 |
| `Catalyst\Helpers\Config\AppEntryCatalog` | `class` | `app/Helpers/Config/AppEntryCatalog.php` | 39 |
| `Catalyst\Helpers\Config\ConfigManager` | `class` | `app/Helpers/Config/ConfigManager.php` | 89 |
| `Catalyst\Helpers\Config\ConfigSecretCatalog` | `class` | `app/Helpers/Config/ConfigSecretCatalog.php` | 39 |
| `Catalyst\Helpers\Config\ConfigSecretStore` | `class` | `app/Helpers/Config/ConfigSecretStore.php` | 41 |
| `Catalyst\Helpers\Debug\ColorType` | `enum` | `app/Helpers/Debug/ColorType.php` | 42 |
| `Catalyst\Helpers\Debug\Dumper` | `class` | `app/Helpers/Debug/Dumper.php` | 44 |
| `Catalyst\Helpers\Debug\DumperCollapsible` | `class` | `app/Helpers/Debug/DumperCollapsible.php` | 42 |
| `Catalyst\Helpers\Debug\DumperColorizer` | `class` | `app/Helpers/Debug/DumperColorizer.php` | 43 |
| `Catalyst\Helpers\Debug\DumperConfig` | `class` | `app/Helpers/Debug/DumperConfig.php` | 43 |
| `Catalyst\Helpers\Debug\DumperPalette` | `class` | `app/Helpers/Debug/DumperPalette.php` | 44 |
| `Catalyst\Helpers\Debug\DumperRenderer` | `class` | `app/Helpers/Debug/DumperRenderer.php` | 44 |
| `Catalyst\Helpers\Debug\Formatters\ArrayFormatter` | `class` | `app/Helpers/Debug/Formatters/ArrayFormatter.php` | 46 |
| `Catalyst\Helpers\Debug\Formatters\ObjectFormatter` | `class` | `app/Helpers/Debug/Formatters/ObjectFormatter.php` | 50 |
| `Catalyst\Helpers\Debug\Formatters\PrimitiveTypeFormatter` | `class` | `app/Helpers/Debug/Formatters/PrimitiveTypeFormatter.php` | 46 |
| `Catalyst\Helpers\Debug\Formatters\ResourceFormatter` | `class` | `app/Helpers/Debug/Formatters/ResourceFormatter.php` | 44 |
| `Catalyst\Helpers\Debug\MainFormatter` | `class` | `app/Helpers/Debug/MainFormatter.php` | 47 |
| `Catalyst\Helpers\Debug\ThemeName` | `enum` | `app/Helpers/Debug/ThemeName.php` | 41 |
| `Catalyst\Helpers\Debug\ThemeProviderInterface` | `interface` | `app/Helpers/Debug/ThemeProviderInterface.php` | 42 |
| `Catalyst\Helpers\Error\ErrorCatcher` | `class` | `app/Helpers/Error/ErrorCatcher.php` | 41 |
| `Catalyst\Helpers\Error\ErrorHandler` | `class` | `app/Helpers/Error/ErrorHandler.php` | 42 |
| `Catalyst\Helpers\Error\ErrorLogger` | `class` | `app/Helpers/Error/ErrorLogger.php` | 42 |
| `Catalyst\Helpers\Error\ErrorOutput` | `class` | `app/Helpers/Error/ErrorOutput.php` | 44 |
| `Catalyst\Helpers\Error\ExceptionHandler` | `class` | `app/Helpers/Error/ExceptionHandler.php` | 49 |
| `Catalyst\Helpers\Error\ShutdownHandler` | `class` | `app/Helpers/Error/ShutdownHandler.php` | 42 |
| `Catalyst\Helpers\Exceptions\ConnectionException` | `class` | `app/Helpers/Exceptions/ConnectionException.php` | 43 |
| `Catalyst\Helpers\Exceptions\EnvironmentException` | `class` | `app/Helpers/Exceptions/EnvironmentException.php` | 46 |
| `Catalyst\Helpers\Exceptions\FileSystemException` | `class` | `app/Helpers/Exceptions/FileSystemException.php` | 43 |
| `Catalyst\Helpers\Exceptions\ForbiddenException` | `class` | `app/Helpers/Exceptions/ForbiddenException.php` | 46 |
| `Catalyst\Helpers\Exceptions\MailException` | `class` | `app/Helpers/Exceptions/MailException.php` | 43 |
| `Catalyst\Helpers\Exceptions\MethodNotAllowedException` | `class` | `app/Helpers/Exceptions/MethodNotAllowedException.php` | 45 |
| `Catalyst\Helpers\Exceptions\ModelNotFoundException` | `class` | `app/Helpers/Exceptions/ModelNotFoundException.php` | 44 |
| `Catalyst\Helpers\Exceptions\OptimisticLockException` | `class` | `app/Helpers/Exceptions/OptimisticLockException.php` | 41 |
| `Catalyst\Helpers\Exceptions\QueryException` | `class` | `app/Helpers/Exceptions/QueryException.php` | 43 |
| `Catalyst\Helpers\Exceptions\RouteNotFoundException` | `class` | `app/Helpers/Exceptions/RouteNotFoundException.php` | 45 |
| `Catalyst\Helpers\Exceptions\ValidationException` | `class` | `app/Helpers/Exceptions/ValidationException.php` | 44 |
| `Catalyst\Helpers\Exceptions\ViewException` | `class` | `app/Helpers/Exceptions/ViewException.php` | 41 |
| `Catalyst\Helpers\I18n\TranslationLoader` | `class` | `app/Helpers/I18n/TranslationLoader.php` | 50 |
| `Catalyst\Helpers\I18n\Translator` | `class` | `app/Helpers/I18n/Translator.php` | 60 |
| `Catalyst\Helpers\IO\FileOutput` | `class` | `app/Helpers/IO/FileOutput.php` | 45 |
| `Catalyst\Helpers\Log\LogRotator` | `class` | `app/Helpers/Log/LogRotator.php` | 39 |
| `Catalyst\Helpers\Log\Logger` | `class` | `app/Helpers/Log/Logger.php` | 42 |
| `Catalyst\Helpers\Log\LoggerConfigurator` | `class` | `app/Helpers/Log/LoggerConfigurator.php` | 39 |
| `Catalyst\Helpers\Log\LoggerContextSanitizer` | `class` | `app/Helpers/Log/LoggerContextSanitizer.php` | 42 |
| `Catalyst\Helpers\Log\LoggerEntryFormatter` | `class` | `app/Helpers/Log/LoggerEntryFormatter.php` | 39 |
| `Catalyst\Helpers\Log\LoggerInlineDisplay` | `class` | `app/Helpers/Log/LoggerInlineDisplay.php` | 42 |
| `Catalyst\Helpers\Log\LoggerLevelMap` | `class` | `app/Helpers/Log/LoggerLevelMap.php` | 39 |
| `Catalyst\Helpers\Log\LoggerRequestClassifier` | `class` | `app/Helpers/Log/LoggerRequestClassifier.php` | 39 |
| `Catalyst\Helpers\Log\LoggerSettings` | `class` | `app/Helpers/Log/LoggerSettings.php` | 39 |
| `Catalyst\Helpers\Log\LoggerWriter` | `class` | `app/Helpers/Log/LoggerWriter.php` | 42 |
| `Catalyst\Helpers\Path\ProjectPath` | `class` | `app/Helpers/Path/ProjectPath.php` | 39 |
| `Catalyst\Helpers\Security\CspNonce` | `class` | `app/Helpers/Security/CspNonce.php` | 46 |
| `Catalyst\Helpers\Security\CsrfProtection` | `class` | `app/Helpers/Security/CsrfProtection.php` | 51 |
| `Catalyst\Helpers\Security\SensitiveValueRedactor` | `class` | `app/Helpers/Security/SensitiveValueRedactor.php` | 39 |
| `Catalyst\Helpers\ToolBox\DrawBox` | `class` | `app/Helpers/ToolBox/DrawBox.php` | 43 |
| `Catalyst\Helpers\ToolBox\DrawBoxCliRenderer` | `class` | `app/Helpers/ToolBox/DrawBoxCliRenderer.php` | 41 |
| `Catalyst\Helpers\ToolBox\DrawBoxFileOutputDecorator` | `class` | `app/Helpers/ToolBox/DrawBoxFileOutputDecorator.php` | 39 |
| `Catalyst\Helpers\ToolBox\DrawBoxHtmlRenderer` | `class` | `app/Helpers/ToolBox/DrawBoxHtmlRenderer.php` | 39 |
| `Catalyst\Helpers\ToolBox\DrawBoxStylePalette` | `class` | `app/Helpers/ToolBox/DrawBoxStylePalette.php` | 39 |
| `Catalyst\Helpers\ToolBox\DrawBoxTextHelper` | `class` | `app/Helpers/ToolBox/DrawBoxTextHelper.php` | 41 |
| `Catalyst\Helpers\Validation\RuleParser` | `class` | `app/Helpers/Validation/RuleParser.php` | 45 |
| `Catalyst\Helpers\Validation\Rules\ComparisonRules` | `class` | `app/Helpers/Validation/Rules/ComparisonRules.php` | 41 |
| `Catalyst\Helpers\Validation\Rules\FileRules` | `class` | `app/Helpers/Validation/Rules/FileRules.php` | 48 |
| `Catalyst\Helpers\Validation\Rules\FormatRules` | `class` | `app/Helpers/Validation/Rules/FormatRules.php` | 41 |
| `Catalyst\Helpers\Validation\Rules\NumericRules` | `class` | `app/Helpers/Validation/Rules/NumericRules.php` | 41 |
| `Catalyst\Helpers\Validation\Rules\StringRules` | `class` | `app/Helpers/Validation/Rules/StringRules.php` | 41 |
| `Catalyst\Helpers\Validation\Rules\UniqueRule` | `class` | `app/Helpers/Validation/Rules/UniqueRule.php` | 48 |
| `Catalyst\Helpers\Validation\ValidationRunner` | `class` | `app/Helpers/Validation/ValidationRunner.php` | 52 |
| `Catalyst\Helpers\Validation\Validator` | `class` | `app/Helpers/Validation/Validator.php` | 39 |
| `Catalyst\Kernel` | `class` | `app/Kernel.php` | 69 |
| `Catalyst\Repository\Account\Controllers\AccountCenterController` | `class` | `Repository/Framework/Account/Controllers/AccountCenterController.php` | 50 |
| `Catalyst\Repository\Account\Controllers\AccountRecoveryController` | `class` | `Repository/Framework/Account/Controllers/AccountRecoveryController.php` | 47 |
| `Catalyst\Repository\Account\Repositories\AccountRecoveryRepository` | `class` | `Repository/Framework/Account/Repositories/AccountRecoveryRepository.php` | 44 |
| `Catalyst\Repository\Account\Requests\MfaRecoveryRequest` | `class` | `Repository/Framework/Account/Requests/MfaRecoveryRequest.php` | 42 |
| `Catalyst\Repository\Account\Requests\SupportRecoveryRequest` | `class` | `Repository/Framework/Account/Requests/SupportRecoveryRequest.php` | 42 |
| `Catalyst\Repository\Account\Services\AccountDashboardService` | `class` | `Repository/Framework/Account/Services/AccountDashboardService.php` | 43 |
| `Catalyst\Repository\Account\Services\AccountRecoveryService` | `class` | `Repository/Framework/Account/Services/AccountRecoveryService.php` | 47 |
| `Catalyst\Repository\Account\Services\AccountSecurityService` | `class` | `Repository/Framework/Account/Services/AccountSecurityService.php` | 43 |
| `Catalyst\Repository\Account\Support\AccountSurfaceViewModel` | `class` | `Repository/Framework/Account/Support/AccountSurfaceViewModel.php` | 42 |
| `Catalyst\Repository\Api\Controllers\CalendarApiController` | `class` | `Repository/Framework/Api/Controllers/CalendarApiController.php` | 23 |
| `Catalyst\Repository\Api\Controllers\CatalogApiController` | `class` | `Repository/Framework/Api/Controllers/CatalogApiController.php` | 12 |
| `Catalyst\Repository\Api\Controllers\VersionApiController` | `class` | `Repository/Framework/Api/Controllers/VersionApiController.php` | 50 |
| `Catalyst\Repository\Api\Controllers\WorkflowApiController` | `class` | `Repository/Framework/Api/Controllers/WorkflowApiController.php` | 49 |
| `Catalyst\Repository\Auth\Controllers\EmailVerificationController` | `class` | `Repository/Framework/Auth/Controllers/EmailVerificationController.php` | 46 |
| `Catalyst\Repository\Auth\Controllers\LoginController` | `class` | `Repository/Framework/Auth/Controllers/LoginController.php` | 47 |
| `Catalyst\Repository\Auth\Controllers\LogoutController` | `class` | `Repository/Framework/Auth/Controllers/LogoutController.php` | 44 |
| `Catalyst\Repository\Auth\Controllers\MfaController` | `class` | `Repository/Framework/Auth/Controllers/MfaController.php` | 49 |
| `Catalyst\Repository\Auth\Controllers\PasswordResetController` | `class` | `Repository/Framework/Auth/Controllers/PasswordResetController.php` | 50 |
| `Catalyst\Repository\Auth\Controllers\RegisterController` | `class` | `Repository/Framework/Auth/Controllers/RegisterController.php` | 50 |
| `Catalyst\Repository\Auth\Controllers\SocialAuthController` | `class` | `Repository/Framework/Auth/Controllers/SocialAuthController.php` | 47 |
| `Catalyst\Repository\Auth\Models\User` | `class` | `Repository/Framework/Auth/Models/User.php` | 51 |
| `Catalyst\Repository\Auth\Requests\EmailVerificationTokenRequest` | `class` | `Repository/Framework/Auth/Requests/EmailVerificationTokenRequest.php` | 44 |
| `Catalyst\Repository\Auth\Requests\MfaCodeRequest` | `class` | `Repository/Framework/Auth/Requests/MfaCodeRequest.php` | 46 |
| `Catalyst\Repository\Configuration\Controllers\AppConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/AppConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\AppearanceController` | `class` | `Repository/Framework/Configuration/Controllers/AppearanceController.php` | 45 |
| `Catalyst\Repository\Configuration\Controllers\CacheConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/CacheConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\ConfigController` | `class` | `Repository/Framework/Configuration/Controllers/ConfigController.php` | 45 |
| `Catalyst\Repository\Configuration\Controllers\CorsConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/CorsConfigSaveController.php` | 50 |
| `Catalyst\Repository\Configuration\Controllers\DbConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/DbConfigSaveController.php` | 46 |
| `Catalyst\Repository\Configuration\Controllers\DevToolsConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/DevToolsConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\DkimController` | `class` | `Repository/Framework/Configuration/Controllers/DkimController.php` | 45 |
| `Catalyst\Repository\Configuration\Controllers\FeatureFlagsController` | `class` | `Repository/Framework/Configuration/Controllers/FeatureFlagsController.php` | 51 |
| `Catalyst\Repository\Configuration\Controllers\FeaturesConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/FeaturesConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\FtpConfigController` | `class` | `Repository/Framework/Configuration/Controllers/FtpConfigController.php` | 46 |
| `Catalyst\Repository\Configuration\Controllers\HealthController` | `class` | `Repository/Framework/Configuration/Controllers/HealthController.php` | 46 |
| `Catalyst\Repository\Configuration\Controllers\LoggingConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/LoggingConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\MailConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/MailConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\PluginsController` | `class` | `Repository/Framework/Configuration/Controllers/PluginsController.php` | 47 |
| `Catalyst\Repository\Configuration\Controllers\SecurityConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/SecurityConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\SessionConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/SessionConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Controllers\SetupCompletionController` | `class` | `Repository/Framework/Configuration/Controllers/SetupCompletionController.php` | 49 |
| `Catalyst\Repository\Configuration\Controllers\WebSocketConfigSaveController` | `class` | `Repository/Framework/Configuration/Controllers/WebSocketConfigSaveController.php` | 44 |
| `Catalyst\Repository\Configuration\Requests\AbstractSettingsRequest` | `class` | `Repository/Framework/Configuration/Requests/AbstractSettingsRequest.php` | 41 |
| `Catalyst\Repository\Configuration\Requests\AppConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/AppConfigRequest.php` | 46 |
| `Catalyst\Repository\Configuration\Requests\AppearanceUpdateRequest` | `class` | `Repository/Framework/Configuration/Requests/AppearanceUpdateRequest.php` | 41 |
| `Catalyst\Repository\Configuration\Requests\CacheConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/CacheConfigRequest.php` | 44 |
| `Catalyst\Repository\Configuration\Requests\CorsConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/CorsConfigRequest.php` | 13 |
| `Catalyst\Repository\Configuration\Requests\DbConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/DbConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\DevToolsConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/DevToolsConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\DkimGenerateRequest` | `class` | `Repository/Framework/Configuration/Requests/DkimGenerateRequest.php` | 13 |
| `Catalyst\Repository\Configuration\Requests\FeatureFlagDefaultRequest` | `class` | `Repository/Framework/Configuration/Requests/FeatureFlagDefaultRequest.php` | 41 |
| `Catalyst\Repository\Configuration\Requests\FeatureFlagOverrideRequest` | `class` | `Repository/Framework/Configuration/Requests/FeatureFlagOverrideRequest.php` | 43 |
| `Catalyst\Repository\Configuration\Requests\FeaturesConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/FeaturesConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\FtpConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/FtpConfigRequest.php` | 13 |
| `Catalyst\Repository\Configuration\Requests\LoggingConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/LoggingConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\MailConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/MailConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\SecurityConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/SecurityConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\SessionConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/SessionConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Requests\SetupPrivilegedAccountRequest` | `class` | `Repository/Framework/Configuration/Requests/SetupPrivilegedAccountRequest.php` | 13 |
| `Catalyst\Repository\Configuration\Requests\WebSocketConfigRequest` | `class` | `Repository/Framework/Configuration/Requests/WebSocketConfigRequest.php` | 39 |
| `Catalyst\Repository\Configuration\Services\SetupDatabaseException` | `class` | `Repository/Framework/Configuration/Services/SetupDatabaseException.php` | 16 |
| `Catalyst\Repository\Configuration\Services\SetupDatabaseService` | `class` | `Repository/Framework/Configuration/Services/SetupDatabaseService.php` | 21 |
| `Catalyst\Repository\Configuration\Services\SetupPrivilegedAccountProvisioner` | `class` | `Repository/Framework/Configuration/Services/SetupPrivilegedAccountProvisioner.php` | 20 |
| `Catalyst\Repository\Configuration\Support\AppConfigWriter` | `class` | `Repository/Framework/Configuration/Support/AppConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\CacheConfigWriter` | `class` | `Repository/Framework/Configuration/Support/CacheConfigWriter.php` | 46 |
| `Catalyst\Repository\Configuration\Support\ConfigurationAccessContract` | `class` | `Repository/Framework/Configuration/Support/ConfigurationAccessContract.php` | 16 |
| `Catalyst\Repository\Configuration\Support\DbConfigWriter` | `class` | `Repository/Framework/Configuration/Support/DbConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\DbConnectivityProbe` | `class` | `Repository/Framework/Configuration/Support/DbConnectivityProbe.php` | 43 |
| `Catalyst\Repository\Configuration\Support\DevToolsConfigWriter` | `class` | `Repository/Framework/Configuration/Support/DevToolsConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\FeaturesConfigWriter` | `class` | `Repository/Framework/Configuration/Support/FeaturesConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\FtpConnectionProbe` | `class` | `Repository/Framework/Configuration/Support/FtpConnectionProbe.php` | 41 |
| `Catalyst\Repository\Configuration\Support\HealthProbeProjector` | `class` | `Repository/Framework/Configuration/Support/HealthProbeProjector.php` | 12 |
| `Catalyst\Repository\Configuration\Support\LoggingConfigWriter` | `class` | `Repository/Framework/Configuration/Support/LoggingConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\MailConfigWriter` | `class` | `Repository/Framework/Configuration/Support/MailConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\PrivilegedAccountReadinessProbe` | `class` | `Repository/Framework/Configuration/Support/PrivilegedAccountReadinessProbe.php` | 42 |
| `Catalyst\Repository\Configuration\Support\SecurityConfigWriter` | `class` | `Repository/Framework/Configuration/Support/SecurityConfigWriter.php` | 41 |
| `Catalyst\Repository\Configuration\Support\SessionConfigWriter` | `class` | `Repository/Framework/Configuration/Support/SessionConfigWriter.php` | 42 |
| `Catalyst\Repository\Configuration\Support\SettingsCardFactory` | `class` | `Repository/Framework/Configuration/Support/SettingsCardFactory.php` | 39 |
| `Catalyst\Repository\Configuration\Support\SettingsDisplayFactory` | `class` | `Repository/Framework/Configuration/Support/SettingsDisplayFactory.php` | 39 |
| `Catalyst\Repository\Configuration\Support\SettingsModalFactory` | `class` | `Repository/Framework/Configuration/Support/SettingsModalFactory.php` | 39 |
| `Catalyst\Repository\Configuration\Support\SettingsPageViewContext` | `class` | `Repository/Framework/Configuration/Support/SettingsPageViewContext.php` | 42 |
| `Catalyst\Repository\Configuration\Support\WebSocketConfigWriter` | `class` | `Repository/Framework/Configuration/Support/WebSocketConfigWriter.php` | 41 |
| `Catalyst\Repository\DemoUi\Controllers\DemoUiController` | `class` | `Repository/Framework/DemoUi/Controllers/DemoUiController.php` | 47 |
| `Catalyst\Repository\DevTools\Controllers\DatabaseResetController` | `class` | `Repository/Framework/DevTools/Controllers/DatabaseResetController.php` | 43 |
| `Catalyst\Repository\DevTools\Controllers\DatabaseTestController` | `class` | `Repository/Framework/DevTools/Controllers/DatabaseTestController.php` | 43 |
| `Catalyst\Repository\DevTools\Controllers\FlashTestController` | `class` | `Repository/Framework/DevTools/Controllers/FlashTestController.php` | 42 |
| `Catalyst\Repository\DevTools\Controllers\FormEventTestController` | `class` | `Repository/Framework/DevTools/Controllers/FormEventTestController.php` | 44 |
| `Catalyst\Repository\DevTools\Controllers\I18nTestController` | `class` | `Repository/Framework/DevTools/Controllers/I18nTestController.php` | 43 |
| `Catalyst\Repository\DevTools\Controllers\InfraTestController` | `class` | `Repository/Framework/DevTools/Controllers/InfraTestController.php` | 45 |
| `Catalyst\Repository\DevTools\Controllers\MailTestController` | `class` | `Repository/Framework/DevTools/Controllers/MailTestController.php` | 43 |
| `Catalyst\Repository\DevTools\Controllers\ModalTestController` | `class` | `Repository/Framework/DevTools/Controllers/ModalTestController.php` | 44 |
| `Catalyst\Repository\DevTools\Controllers\OrmTestController` | `class` | `Repository/Framework/DevTools/Controllers/OrmTestController.php` | 45 |
| `Catalyst\Repository\DevTools\Controllers\RbacTestController` | `class` | `Repository/Framework/DevTools/Controllers/RbacTestController.php` | 45 |
| `Catalyst\Repository\DevTools\Controllers\RouteTestController` | `class` | `Repository/Framework/DevTools/Controllers/RouteTestController.php` | 46 |
| `Catalyst\Repository\DevTools\Controllers\TestFeaturesController` | `class` | `Repository/Framework/DevTools/Controllers/TestFeaturesController.php` | 43 |
| `Catalyst\Repository\DevTools\Controllers\ToasterTestController` | `class` | `Repository/Framework/DevTools/Controllers/ToasterTestController.php` | 44 |
| `Catalyst\Repository\DevTools\Controllers\UmlController` | `class` | `Repository/Framework/DevTools/Controllers/UmlController.php` | 44 |
| `Catalyst\Repository\DevTools\Controllers\UploadTestController` | `class` | `Repository/Framework/DevTools/Controllers/UploadTestController.php` | 44 |
| `Catalyst\Repository\DevTools\Controllers\ValidatorTestController` | `class` | `Repository/Framework/DevTools/Controllers/ValidatorTestController.php` | 42 |
| `Catalyst\Repository\DevTools\Models\DemoEmail` | `class` | `Repository/Framework/DevTools/Models/DemoEmail.php` | 41 |
| `Catalyst\Repository\DevTools\Services\DatabaseResetService` | `class` | `Repository/Framework/DevTools/Services/DatabaseResetService.php` | 45 |
| `Catalyst\Repository\Notification\Controllers\NotificationController` | `class` | `Repository/Framework/Notification/Controllers/NotificationController.php` | 46 |
| `Catalyst\Repository\Notification\Controllers\PresenceController` | `class` | `Repository/Framework/Notification/Controllers/PresenceController.php` | 47 |
| `Catalyst\Repository\Operations\ApiManagement\Controllers\ApiManagementController` | `class` | `Repository/Framework/Operations/ApiManagement/Controllers/ApiManagementController.php` | 52 |
| `Catalyst\Repository\Operations\ApiManagement\Requests\ApiTokenRequest` | `class` | `Repository/Framework/Operations/ApiManagement/Requests/ApiTokenRequest.php` | 48 |
| `Catalyst\Repository\Operations\Audit\Controllers\AuditLogController` | `class` | `Repository/Framework/Operations/Audit/Controllers/AuditLogController.php` | 46 |
| `Catalyst\Repository\Operations\Automation\Actions\AutomationRuleExecutionService` | `class` | `Repository/Framework/Operations/Automation/Actions/AutomationRuleExecutionService.php` | 46 |
| `Catalyst\Repository\Operations\Automation\Actions\AutomationRuleMutationService` | `class` | `Repository/Framework/Operations/Automation/Actions/AutomationRuleMutationService.php` | 47 |
| `Catalyst\Repository\Operations\Automation\Controllers\AutomationRuleApiController` | `class` | `Repository/Framework/Operations/Automation/Controllers/AutomationRuleApiController.php` | 54 |
| `Catalyst\Repository\Operations\Automation\Controllers\AutomationRuleController` | `class` | `Repository/Framework/Operations/Automation/Controllers/AutomationRuleController.php` | 61 |
| `Catalyst\Repository\Operations\Automation\Requests\AutomationRuleIndexRequest` | `class` | `Repository/Framework/Operations/Automation/Requests/AutomationRuleIndexRequest.php` | 41 |
| `Catalyst\Repository\Operations\Automation\Requests\AutomationRuleRequest` | `class` | `Repository/Framework/Operations/Automation/Requests/AutomationRuleRequest.php` | 50 |
| `Catalyst\Repository\Operations\Automation\Requests\AutomationRuleTransitionRequest` | `class` | `Repository/Framework/Operations/Automation/Requests/AutomationRuleTransitionRequest.php` | 41 |
| `Catalyst\Repository\Operations\Automation\Requests\AutomationRunContextRequest` | `class` | `Repository/Framework/Operations/Automation/Requests/AutomationRunContextRequest.php` | 44 |
| `Catalyst\Repository\Operations\Automation\Support\AutomationManualRunState` | `class` | `Repository/Framework/Operations/Automation/Support/AutomationManualRunState.php` | 41 |
| `Catalyst\Repository\Operations\Automation\Support\AutomationRuleFormFactory` | `class` | `Repository/Framework/Operations/Automation/Support/AutomationRuleFormFactory.php` | 41 |
| `Catalyst\Repository\Operations\Automation\Support\AutomationRuleGridFactory` | `class` | `Repository/Framework/Operations/Automation/Support/AutomationRuleGridFactory.php` | 45 |
| `Catalyst\Repository\Operations\Automation\Support\AutomationRuleShowDataFactory` | `class` | `Repository/Framework/Operations/Automation/Support/AutomationRuleShowDataFactory.php` | 47 |
| `Catalyst\Repository\Operations\Deployments\Actions\DeploymentExecutionService` | `class` | `Repository/Framework/Operations/Deployments/Actions/DeploymentExecutionService.php` | 15 |
| `Catalyst\Repository\Operations\Deployments\Controllers\DeploymentsController` | `class` | `Repository/Framework/Operations/Deployments/Controllers/DeploymentsController.php` | 21 |
| `Catalyst\Repository\Operations\Deployments\Requests\DeploymentRunRequest` | `class` | `Repository/Framework/Operations/Deployments/Requests/DeploymentRunRequest.php` | 15 |
| `Catalyst\Repository\Operations\Deployments\Support\DeploymentFormFactory` | `class` | `Repository/Framework/Operations/Deployments/Support/DeploymentFormFactory.php` | 12 |
| `Catalyst\Repository\Operations\Deployments\Support\DeploymentGridFactory` | `class` | `Repository/Framework/Operations/Deployments/Support/DeploymentGridFactory.php` | 13 |
| `Catalyst\Repository\Operations\Support\OperationsAccessContract` | `class` | `Repository/Framework/Operations/Support/OperationsAccessContract.php` | 17 |
| `Catalyst\Repository\Operations\Tenancy\Controllers\TenancyController` | `class` | `Repository/Framework/Operations/Tenancy/Controllers/TenancyController.php` | 16 |
| `Catalyst\Repository\Operations\Tenancy\Support\TenancyDiagnosticProjector` | `class` | `Repository/Framework/Operations/Tenancy/Support/TenancyDiagnosticProjector.php` | 10 |
| `Catalyst\Repository\Users\Controllers\AccountRecoveryReviewController` | `class` | `Repository/Framework/Users/Controllers/AccountRecoveryReviewController.php` | 47 |
| `Catalyst\Repository\Users\Controllers\OrganizationHierarchyController` | `class` | `Repository/Framework/Users/Controllers/OrganizationHierarchyController.php` | 20 |
| `Catalyst\Repository\Users\Controllers\PermissionsController` | `class` | `Repository/Framework/Users/Controllers/PermissionsController.php` | 51 |
| `Catalyst\Repository\Users\Controllers\RolesController` | `class` | `Repository/Framework/Users/Controllers/RolesController.php` | 53 |
| `Catalyst\Repository\Users\Controllers\UserManagementController` | `class` | `Repository/Framework/Users/Controllers/UserManagementController.php` | 52 |
| `Catalyst\Repository\Users\Controllers\UserRolesController` | `class` | `Repository/Framework/Users/Controllers/UserRolesController.php` | 46 |
| `Catalyst\Repository\Users\Requests\PermissionBulkSelectionRequest` | `class` | `Repository/Framework/Users/Requests/PermissionBulkSelectionRequest.php` | 41 |
| `Catalyst\Repository\Users\Requests\PermissionPayloadRequest` | `class` | `Repository/Framework/Users/Requests/PermissionPayloadRequest.php` | 43 |
| `Catalyst\Repository\Users\Requests\RoleBulkSelectionRequest` | `class` | `Repository/Framework/Users/Requests/RoleBulkSelectionRequest.php` | 41 |
| `Catalyst\Repository\Users\Requests\RolePayloadRequest` | `class` | `Repository/Framework/Users/Requests/RolePayloadRequest.php` | 46 |
| `Catalyst\Repository\Users\Requests\RolePermissionSyncRequest` | `class` | `Repository/Framework/Users/Requests/RolePermissionSyncRequest.php` | 41 |
| `Catalyst\Repository\Users\Requests\UserEnrollmentRequest` | `class` | `Repository/Framework/Users/Requests/UserEnrollmentRequest.php` | 42 |
| `Catalyst\Repository\Users\Support\RbacLabelPresenter` | `class` | `Repository/Framework/Users/Support/RbacLabelPresenter.php` | 39 |
| `Catalyst\Repository\Users\Support\UserEnrollmentFormFactory` | `class` | `Repository/Framework/Users/Support/UserEnrollmentFormFactory.php` | 43 |
| `Catalyst\Repository\Workspaces\Catalogs\Actions\CatalogMutationService` | `class` | `Repository/Framework/Workspaces/Catalogs/Actions/CatalogMutationService.php` | 49 |
| `Catalyst\Repository\Workspaces\Catalogs\Controllers\CatalogController` | `class` | `Repository/Framework/Workspaces/Catalogs/Controllers/CatalogController.php` | 62 |
| `Catalyst\Repository\Workspaces\Catalogs\Requests\CatalogDefinitionRequest` | `class` | `Repository/Framework/Workspaces/Catalogs/Requests/CatalogDefinitionRequest.php` | 48 |
| `Catalyst\Repository\Workspaces\Catalogs\Requests\CatalogItemRequest` | `class` | `Repository/Framework/Workspaces/Catalogs/Requests/CatalogItemRequest.php` | 49 |
| `Catalyst\Repository\Workspaces\Catalogs\Support\CatalogFormFactory` | `class` | `Repository/Framework/Workspaces/Catalogs/Support/CatalogFormFactory.php` | 41 |
| `Catalyst\Repository\Workspaces\Catalogs\Support\CatalogGridFactory` | `class` | `Repository/Framework/Workspaces/Catalogs/Support/CatalogGridFactory.php` | 43 |
| `Catalyst\Repository\Workspaces\Documents\Actions\DocumentTemplateExportService` | `class` | `Repository/Framework/Workspaces/Documents/Actions/DocumentTemplateExportService.php` | 43 |
| `Catalyst\Repository\Workspaces\Documents\Actions\DocumentTemplateMutationService` | `class` | `Repository/Framework/Workspaces/Documents/Actions/DocumentTemplateMutationService.php` | 47 |
| `Catalyst\Repository\Workspaces\Documents\Actions\DocumentTemplatePreviewService` | `class` | `Repository/Framework/Workspaces/Documents/Actions/DocumentTemplatePreviewService.php` | 42 |
| `Catalyst\Repository\Workspaces\Documents\Controllers\DocumentTemplateApiController` | `class` | `Repository/Framework/Workspaces/Documents/Controllers/DocumentTemplateApiController.php` | 53 |
| `Catalyst\Repository\Workspaces\Documents\Controllers\DocumentTemplateController` | `class` | `Repository/Framework/Workspaces/Documents/Controllers/DocumentTemplateController.php` | 59 |
| `Catalyst\Repository\Workspaces\Documents\Requests\DocumentExportPayloadRequest` | `class` | `Repository/Framework/Workspaces/Documents/Requests/DocumentExportPayloadRequest.php` | 39 |
| `Catalyst\Repository\Workspaces\Documents\Requests\DocumentPreviewPayloadRequest` | `class` | `Repository/Framework/Workspaces/Documents/Requests/DocumentPreviewPayloadRequest.php` | 44 |
| `Catalyst\Repository\Workspaces\Documents\Requests\DocumentTemplateIndexRequest` | `class` | `Repository/Framework/Workspaces/Documents/Requests/DocumentTemplateIndexRequest.php` | 41 |
| `Catalyst\Repository\Workspaces\Documents\Requests\DocumentTemplateRequest` | `class` | `Repository/Framework/Workspaces/Documents/Requests/DocumentTemplateRequest.php` | 48 |
| `Catalyst\Repository\Workspaces\Documents\Requests\DocumentTemplateTransitionRequest` | `class` | `Repository/Framework/Workspaces/Documents/Requests/DocumentTemplateTransitionRequest.php` | 41 |
| `Catalyst\Repository\Workspaces\Documents\Support\DocumentPreviewState` | `class` | `Repository/Framework/Workspaces/Documents/Support/DocumentPreviewState.php` | 41 |
| `Catalyst\Repository\Workspaces\Documents\Support\DocumentTemplateFormFactory` | `class` | `Repository/Framework/Workspaces/Documents/Support/DocumentTemplateFormFactory.php` | 41 |
| `Catalyst\Repository\Workspaces\Documents\Support\DocumentTemplateGridFactory` | `class` | `Repository/Framework/Workspaces/Documents/Support/DocumentTemplateGridFactory.php` | 43 |
| `Catalyst\Repository\Workspaces\Documents\Support\DocumentTemplateShowDataFactory` | `class` | `Repository/Framework/Workspaces/Documents/Support/DocumentTemplateShowDataFactory.php` | 44 |
| `Catalyst\Repository\Workspaces\Localization\Controllers\LocalizationController` | `class` | `Repository/Framework/Workspaces/Localization/Controllers/LocalizationController.php` | 16 |
| `Catalyst\Repository\Workspaces\Localization\Requests\LocaleCreateRequest` | `class` | `Repository/Framework/Workspaces/Localization/Requests/LocaleCreateRequest.php` | 12 |
| `Catalyst\Repository\Workspaces\Localization\Requests\LocaleSyncRequest` | `class` | `Repository/Framework/Workspaces/Localization/Requests/LocaleSyncRequest.php` | 12 |
| `Catalyst\Repository\Workspaces\Localization\Requests\LocalizationSettingsRequest` | `class` | `Repository/Framework/Workspaces/Localization/Requests/LocalizationSettingsRequest.php` | 12 |
| `Catalyst\Repository\Workspaces\Media\Controllers\MediaLibraryController` | `class` | `Repository/Framework/Workspaces/Media/Controllers/MediaLibraryController.php` | 53 |
| `Catalyst\Repository\Workspaces\Media\Controllers\MetadataFieldController` | `class` | `Repository/Framework/Workspaces/Media/Controllers/MetadataFieldController.php` | 53 |
| `Catalyst\Repository\Workspaces\Media\Requests\MediaBulkSelectionRequest` | `class` | `Repository/Framework/Workspaces/Media/Requests/MediaBulkSelectionRequest.php` | 41 |
| `Catalyst\Repository\Workspaces\Media\Requests\MediaItemRequest` | `class` | `Repository/Framework/Workspaces/Media/Requests/MediaItemRequest.php` | 49 |
| `Catalyst\Repository\Workspaces\Media\Requests\MetadataFieldDefinitionRequest` | `class` | `Repository/Framework/Workspaces/Media/Requests/MetadataFieldDefinitionRequest.php` | 47 |
| `Catalyst\Repository\Workspaces\Media\Support\MediaLibraryFormFactory` | `class` | `Repository/Framework/Workspaces/Media/Support/MediaLibraryFormFactory.php` | 44 |
| `Catalyst\Repository\Workspaces\Media\Support\MetadataFieldFormFactory` | `class` | `Repository/Framework/Workspaces/Media/Support/MetadataFieldFormFactory.php` | 41 |
| `Catalyst\Repository\Workspaces\ModuleDesigner\Controllers\ModuleDesignerController` | `class` | `Repository/Framework/Workspaces/ModuleDesigner/Controllers/ModuleDesignerController.php` | 21 |
| `Catalyst\Repository\Workspaces\ModuleDesigner\Requests\ModuleDesignerRequest` | `class` | `Repository/Framework/Workspaces/ModuleDesigner/Requests/ModuleDesignerRequest.php` | 20 |
| `Catalyst\Repository\Workspaces\ModuleDesigner\Support\ModuleDesignerPreviewToken` | `class` | `Repository/Framework/Workspaces/ModuleDesigner/Support/ModuleDesignerPreviewToken.php` | 12 |
| `Catalyst\Repository\Workspaces\Support\WorkspacesAccessContract` | `class` | `Repository/Framework/Workspaces/Support/WorkspacesAccessContract.php` | 17 |

## Templates

| File | Root | Extension |
|---|---|---|
| `boot-core/template/components/form-builder/_field-block.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/components/form-builder/_field-control.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/components/_datagrid-cell.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/components/_datagrid.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/components/_form-builder.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/components/_page-header.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/components/_record-presence.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/document.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/errors/surface.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/exports/datagrid-xls.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/scope/components/_datagrid.php` | `boot-core/template` | `php` |
| `boot-core/template/scope/components/_form-builder.php` | `boot-core/template` | `php` |
| `boot-core/template/scope/components/_page-header.php` | `boot-core/template` | `php` |
| `boot-core/template/scope/components/_record-presence.php` | `boot-core/template` | `php` |
| `boot-core/template/shell.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_body-scripts.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_content.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_head-assets.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_head-meta.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_head.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_html-open.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_sidebar-node.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_sidebar.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_status-bar.phtml` | `boot-core/template` | `phtml` |
| `boot-core/template/_topbar.phtml` | `boot-core/template` | `phtml` |
| `Repository/App/Surface/Dashboard/Views/pages/guest.phtml` | `Repository views` | `phtml` |
| `Repository/App/Surface/Dashboard/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/App/Surface/Dashboard/Views/pages/surface.phtml` | `Repository views` | `phtml` |
| `Repository/App/Surface/Home/Views/pages/surface.phtml` | `Repository views` | `phtml` |
| `Repository/App/Surface/Landing/Views/pages/surface.phtml` | `Repository views` | `phtml` |
| `Repository/App/Surface/Store/Views/pages/surface.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/activity.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/mfa-recovery.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/mfa-request.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/mfa.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/profile.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/recovery-start.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/recovery.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/security.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/support-request.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Account/Views/pages/support.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/forgot-password.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/login.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/mfa-challenge.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/mfa-setup.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/register.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/reset-password.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/pages/verify-email.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/partials/_auth-social.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Auth/Views/scope/pages/forgot-password.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/pages/login.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/pages/mfa-challenge.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/pages/mfa-setup.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/pages/register.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/pages/reset-password.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/pages/verify-email.php` | `Repository views` | `php` |
| `Repository/Framework/Auth/Views/scope/partials/_auth-social.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/pages/appearance.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/pages/feature-flags.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/pages/health.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/pages/plugins.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-card.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-dkim-card.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-grid.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-modal-field.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-modal.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-modals.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-row.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/partials/_settings-setup-actions.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Configuration/Views/scope/pages/appearance.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/scope/pages/feature-flags.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/scope/pages/health.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/scope/pages/plugins.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/scope/partials/_settings-dkim-card.php` | `Repository views` | `php` |
| `Repository/Framework/Configuration/Views/scope/partials/_settings-setup-actions.php` | `Repository views` | `php` |
| `Repository/Framework/DemoUi/Views/pages/demo-ui.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/pages/layout-test.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/pages/route-test.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/pages/test-features.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/pages/uml.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/modal/_form-content.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/modal/_sample-content.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/toaster/_js-enhancement-refresh.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/uml/_rail.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/uml/_tab-panels.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-activity.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-auth.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-database.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-endpoints.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-file-upload.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-flash.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-form-events.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-i18n.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-infrastructure.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-js-enhancements.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-json-inspection.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-mail.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-modals.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-orm.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-rbac.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-system-info.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-toasters.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/partials/_tf-validator.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/DevTools/Views/scope/pages/route-test.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/pages/test-features.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/pages/uml.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/modal/_form-content.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/_tf-auth.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/_tf-database.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/_tf-file-upload.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/_tf-i18n.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/_tf-rbac.php` | `Repository views` | `php` |
| `Repository/Framework/DevTools/Views/scope/partials/_tf-system-info.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/ApiManagement/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/ApiManagement/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Audit/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Audit/Views/pages/show.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Audit/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Audit/Views/scope/pages/show.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Automation/Views/pages/form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Automation/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Automation/Views/pages/show.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Automation/Views/scope/pages/form.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Automation/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Automation/Views/scope/pages/show.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Deployments/Views/pages/deployments.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Deployments/Views/scope/pages/deployments.php` | `Repository views` | `php` |
| `Repository/Framework/Operations/Tenancy/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Operations/Tenancy/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/pages/form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/organization-hierarchy.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/permission-form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/permissions-list.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/permissions.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/recovery-requests.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/recovery-review.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/user-register.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/user-roles.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/pages/users-index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Users/Views/scope/pages/form.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/organization-hierarchy.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/permission-form.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/permissions-list.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/permissions.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/user-register.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/user-roles.php` | `Repository views` | `php` |
| `Repository/Framework/Users/Views/scope/pages/users-index.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Catalogs/Views/pages/form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Catalogs/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Catalogs/Views/pages/item-form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Catalogs/Views/pages/show.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Catalogs/Views/scope/pages/form.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Catalogs/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Catalogs/Views/scope/pages/item-form.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Catalogs/Views/scope/pages/show.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Documents/Views/pages/form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Documents/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Documents/Views/pages/show.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Documents/Views/scope/pages/form.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Documents/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Documents/Views/scope/pages/show.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Media/Views/pages/field-form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Media/Views/pages/fields-index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Media/Views/pages/form.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Media/Views/pages/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Media/Views/scope/pages/field-form.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Media/Views/scope/pages/fields-index.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Media/Views/scope/pages/form.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Media/Views/scope/pages/index.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Views/pages/localization/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Views/pages/module-designer/index.phtml` | `Repository views` | `phtml` |
| `Repository/Framework/Workspaces/Views/scope/pages/localization/index.php` | `Repository views` | `php` |
| `Repository/Framework/Workspaces/Views/scope/pages/module-designer/index.php` | `Repository views` | `php` |

## Scripts

| File | Root | Bytes |
|---|---|---:|
| `Repository/App/Surface/Dashboard/front/script.js` | `Repository front` | 11 |
| `Repository/App/Surface/Home/front/script.js` | `Repository front` | 11 |
| `Repository/App/Surface/Landing/front/script.js` | `Repository front` | 11 |
| `Repository/App/Surface/Store/front/script.js` | `Repository front` | 11 |
| `Repository/Framework/Account/front/script.js` | `Repository front` | 11 |
| `Repository/Framework/Auth/front/script.js` | `Repository front` | 2073 |
| `Repository/Framework/Configuration/front/script.js` | `Repository front` | 12479 |
| `Repository/Framework/DemoUi/front/script.js` | `Repository front` | 1225 |
| `Repository/Framework/DevTools/front/script.js` | `Repository front` | 23683 |
| `Repository/Framework/Operations/front/script.js` | `Repository front` | 132 |
| `Repository/Framework/Users/front/script.js` | `Repository front` | 255 |
| `Repository/Framework/Workspaces/front/script.js` | `Repository front` | 366 |
| `public/assets/js/catalyst/appearance-bootstrap.js` | `public catalyst js` | 5802 |
| `public/assets/js/catalyst/bootstrap/components.js` | `public catalyst js` | 10368 |
| `public/assets/js/catalyst/bootstrap/primitives.js` | `public catalyst js` | 1245 |
| `public/assets/js/catalyst/catalyst.js` | `public catalyst js` | 11030 |
| `public/assets/js/catalyst/config/defaults.js` | `public catalyst js` | 3185 |
| `public/assets/js/catalyst/core/asset-loader.js` | `public catalyst js` | 2758 |
| `public/assets/js/catalyst/core/declarative-actions.js` | `public catalyst js` | 2684 |
| `public/assets/js/catalyst/core/http.js` | `public catalyst js` | 11831 |
| `public/assets/js/catalyst/core/loading.js` | `public catalyst js` | 1789 |
| `public/assets/js/catalyst/core/response-actions.js` | `public catalyst js` | 1315 |
| `public/assets/js/catalyst/core/trusted-dom.js` | `public catalyst js` | 919 |
| `public/assets/js/catalyst/core/utils.js` | `public catalyst js` | 6387 |
| `public/assets/js/catalyst/datagrid/interactions.js` | `public catalyst js` | 3156 |
| `public/assets/js/catalyst/forms/builder.js` | `public catalyst js` | 11565 |
| `public/assets/js/catalyst/forms/form-handler.js` | `public catalyst js` | 12234 |
| `public/assets/js/catalyst/forms/password.js` | `public catalyst js` | 9356 |
| `public/assets/js/catalyst/forms/validation.js` | `public catalyst js` | 707 |
| `public/assets/js/catalyst/inspinia/card-actions.js` | `public catalyst js` | 2933 |
| `public/assets/js/catalyst/inspinia/charts.js` | `public catalyst js` | 10660 |
| `public/assets/js/catalyst/inspinia/code-preview.js` | `public catalyst js` | 4077 |
| `public/assets/js/catalyst/inspinia/enhancers/editors.js` | `public catalyst js` | 322 |
| `public/assets/js/catalyst/inspinia/enhancers/engine.js` | `public catalyst js` | 50938 |
| `public/assets/js/catalyst/inspinia/enhancers/index.js` | `public catalyst js` | 482 |
| `public/assets/js/catalyst/inspinia/enhancers/pickers.js` | `public catalyst js` | 648 |
| `public/assets/js/catalyst/inspinia/enhancers/selects.js` | `public catalyst js` | 344 |
| `public/assets/js/catalyst/inspinia/enhancers/sliders.js` | `public catalyst js` | 475 |
| `public/assets/js/catalyst/inspinia/enhancers/uploads.js` | `public catalyst js` | 322 |
| `public/assets/js/catalyst/inspinia/enhancers/wizard.js` | `public catalyst js` | 281 |
| `public/assets/js/catalyst/inspinia/tables.js` | `public catalyst js` | 12552 |
| `public/assets/js/catalyst/notifications/flash.js` | `public catalyst js` | 5093 |
| `public/assets/js/catalyst/notifications/modal.js` | `public catalyst js` | 20322 |
| `public/assets/js/catalyst/notifications/notification.js` | `public catalyst js` | 4509 |
| `public/assets/js/catalyst/notifications/toaster.js` | `public catalyst js` | 11394 |
| `public/assets/js/catalyst/presence/record-presence.js` | `public catalyst js` | 6279 |
| `public/assets/js/catalyst/runtime/activity-manager.js` | `public catalyst js` | 6600 |
| `public/assets/js/catalyst/runtime/component-registry.js` | `public catalyst js` | 2114 |
| `public/assets/js/catalyst/runtime/event-registry.js` | `public catalyst js` | 995 |
| `public/assets/js/catalyst/runtime/registration-queue.js` | `public catalyst js` | 1957 |
| `public/assets/js/catalyst/runtime/ui-runtime.js` | `public catalyst js` | 20214 |
| `public/assets/js/catalyst/shell/navigation.js` | `public catalyst js` | 6297 |
| `public/assets/js/catalyst/shell/simplebar.js` | `public catalyst js` | 1730 |
| `public/assets/js/catalyst/shell/status-bar.js` | `public catalyst js` | 19778 |
| `public/assets/js/catalyst/shell/theme-customizer.js` | `public catalyst js` | 12390 |
| `public/assets/js/catalyst/shell/topbar.js` | `public catalyst js` | 1942 |
