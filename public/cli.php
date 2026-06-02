<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * CLI Entry Point
 *
 * @package   Catalyst
 * @author    Walter Nuñez (arcanisgk) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 * @link      https://catalyst.lh-2.net
 */

/**
 * Bootstrap — load error-catcher if not already initialised.
 * In CLI mode .htaccess / .user.ini do not apply, so this ensures
 * constants like IS_CLI, PD, DS, NL are available before anything else.
 */
if (!defined('INITIALIZED_BUG_CATCHER')) {
    $errorCatcherPath = __DIR__ . '/../boot-core/requirement-loader/error-catcher.php';
    if (file_exists($errorCatcherPath)) {
        require_once $errorCatcherPath;
    } else {
        die('CRITICAL ERROR: Error handling system not found. Application cannot start.' . PHP_EOL);
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

// Block web access
if (!IS_CLI) {
    header('HTTP/1.1 403 Forbidden');
    echo '<!DOCTYPE html><html lang="en"><body>';
    echo '<h1>403 Forbidden</h1>';
    echo '<p>This script can only be executed from the command line.</p>';
    echo '</body></html>';
    exit(1);
}

use Catalyst\Framework\Cli\CliKernel;
use Catalyst\Framework\Cli\CommandRegistry;
use Catalyst\Framework\Cli\Commands\HelpCommand;
use Catalyst\Framework\Cli\Commands\GeoSmokeCommand;
use Catalyst\Framework\Cli\Commands\IdempotencySmokeCommand;
use Catalyst\Framework\Cli\Commands\I18nInitLocaleCommand;
use Catalyst\Framework\Cli\Commands\I18nStatusCommand;
use Catalyst\Framework\Cli\Commands\I18nSyncCommand;
use Catalyst\Framework\Cli\Commands\AttachmentsListCommand;
use Catalyst\Framework\Cli\Commands\AttachmentsSmokeCommand;
use Catalyst\Framework\Cli\Commands\ApiTokensSmokeCommand;
use Catalyst\Framework\Cli\Commands\DocsSyncRuntimeCommand;
use Catalyst\Framework\Cli\Commands\FixturesAuthCommand;
use Catalyst\Framework\Cli\Commands\FeatureFlagsListCommand;
use Catalyst\Framework\Cli\Commands\FeatureFlagsSetCommand;
use Catalyst\Framework\Cli\Commands\ExportDevelopmentOverlayCommand;
use Catalyst\Framework\Cli\Commands\InspectHarnessCommand;
use Catalyst\Framework\Cli\Commands\InspectLintCommand;
use Catalyst\Framework\Cli\Commands\InspectModuleCommand;
use Catalyst\Framework\Cli\Commands\InspectModulesCommand;
use Catalyst\Framework\Cli\Commands\DeployListCommand;
use Catalyst\Framework\Cli\Commands\DeployRunCommand;
use Catalyst\Framework\Cli\Commands\PluginListCommand;
use Catalyst\Framework\Cli\Commands\PluginToggleCommand;
use Catalyst\Framework\Cli\Commands\PresenceSmokeCommand;
use Catalyst\Framework\Cli\Commands\TenancySmokeCommand;
use Catalyst\Framework\Cli\Commands\TenancyStatusCommand;
use Catalyst\Framework\Cli\Commands\TimelineSmokeCommand;
use Catalyst\Framework\Cli\Commands\VersionCommand;
use Catalyst\Framework\Cli\Commands\StatusCommand;
use Catalyst\Framework\Cli\Commands\CacheBuildCommand;
use Catalyst\Framework\Cli\Commands\CacheClearCommand;
use Catalyst\Framework\Cli\Commands\ClaimsListCommand;
use Catalyst\Framework\Cli\Commands\ClaimsReleaseCommand;
use Catalyst\Framework\Cli\Commands\ConfigSecretsSyncCommand;
use Catalyst\Framework\Cli\Commands\ConcurrencySmokeCommand;
use Catalyst\Framework\Cli\Commands\QueueFailedCommand;
use Catalyst\Framework\Cli\Commands\QueueRetryCommand;
use Catalyst\Framework\Cli\Commands\QueueWorkCommand;
use Catalyst\Framework\Cli\Commands\QualityCheckCommand;
use Catalyst\Framework\Cli\Commands\RouteCacheCommand;
use Catalyst\Framework\Cli\Commands\RouteBootstrapRegressionCommand;
use Catalyst\Framework\Cli\Commands\RouteClearCommand;
use Catalyst\Framework\Cli\Commands\RouteLintCommand;
use Catalyst\Framework\Cli\Commands\RouteListCommand;
use Catalyst\Framework\Cli\Commands\SecurityRegressionCommand;
use Catalyst\Framework\Cli\Commands\CatalogsSmokeCommand;
use Catalyst\Framework\Cli\Commands\ReportingRunCommand;
use Catalyst\Framework\Cli\Commands\ReportingSmokeCommand;
use Catalyst\Framework\Cli\Commands\RetentionRunCommand;
use Catalyst\Framework\Cli\Commands\RetentionSmokeCommand;
use Catalyst\Framework\Cli\Commands\ScheduleListCommand;
use Catalyst\Framework\Cli\Commands\ScheduleRunCommand;
use Catalyst\Framework\Cli\Commands\SensitivitySmokeCommand;
use Catalyst\Framework\Cli\Commands\TemporalSmokeCommand;
use Catalyst\Framework\Cli\Commands\MakeControllerCommand;
use Catalyst\Framework\Cli\Commands\MakeCommandCommand;
use Catalyst\Framework\Cli\Commands\MakeCrudCommand;
use Catalyst\Framework\Cli\Commands\MakeModelCommand;
use Catalyst\Framework\Cli\Commands\MakeMiddlewareCommand;
use Catalyst\Framework\Cli\Commands\MakeMigrationCommand;
use Catalyst\Framework\Cli\Commands\MakeModuleCommand;
use Catalyst\Framework\Cli\Commands\MakePolicyCommand;
use Catalyst\Framework\Cli\Commands\MakeRequestCommand;
use Catalyst\Framework\Cli\Commands\MigrateCommand;
use Catalyst\Framework\Cli\Commands\MigrateRollbackCommand;
use Catalyst\Framework\Cli\Commands\MigrateStatusCommand;
use Catalyst\Framework\Cli\Commands\ConfigShowCommand;
use Catalyst\Framework\Cli\Commands\DevToolsDisableCommand;
use Catalyst\Framework\Cli\Commands\KeyGenerateCommand;
use Catalyst\Framework\Cli\Commands\SecurityCheckCommand;
use Catalyst\Framework\Cli\Commands\StorageCleanCommand;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Helpers\I18n\Translator;

Translator::getInstance()->init(
    (string) (GET_ENV_VAR['APP_LANG'] ?? 'en'),
    PD . DS . 'boot-core' . DS . 'lang'
);

ModuleRegistry::getInstance()->all();

// Register built-in commands
$registry = CommandRegistry::getInstance();
$registry
    ->register(new HelpCommand())
    ->register(new GeoSmokeCommand())
    ->register(new IdempotencySmokeCommand())
    ->register(new I18nStatusCommand())
    ->register(new I18nInitLocaleCommand())
    ->register(new I18nSyncCommand())
    ->register(new AttachmentsListCommand())
    ->register(new AttachmentsSmokeCommand())
    ->register(new ApiTokensSmokeCommand())
    ->register(new FeatureFlagsListCommand())
    ->register(new FeatureFlagsSetCommand())
    ->register(new ExportDevelopmentOverlayCommand())
    ->register(new DocsSyncRuntimeCommand())
    ->register(new DeployListCommand())
    ->register(new DeployRunCommand())
    ->register(new CatalogsSmokeCommand())
    ->register(new FixturesAuthCommand())
    ->register(new InspectHarnessCommand())
    ->register(new InspectModulesCommand())
    ->register(new InspectModuleCommand())
    ->register(new InspectLintCommand())
    ->register(new PluginListCommand())
    ->register(new PluginToggleCommand())
    ->register(new PresenceSmokeCommand())
    ->register(new TenancySmokeCommand())
    ->register(new TenancyStatusCommand())
    ->register(new TimelineSmokeCommand())
    ->register(new VersionCommand())
    ->register(new StatusCommand())
    ->register(new CacheBuildCommand())
    ->register(new CacheClearCommand())
    ->register(new ClaimsListCommand())
    ->register(new ClaimsReleaseCommand())
    ->register(new ConfigSecretsSyncCommand())
    ->register(new ConcurrencySmokeCommand())
    ->register(new ConfigShowCommand())
    ->register(new DevToolsDisableCommand())
    ->register(new KeyGenerateCommand())
    ->register(new QueueWorkCommand())
    ->register(new QueueFailedCommand())
    ->register(new QueueRetryCommand())
    ->register(new QualityCheckCommand())
    ->register(new ReportingRunCommand())
    ->register(new ReportingSmokeCommand())
    ->register(new RetentionRunCommand())
    ->register(new RetentionSmokeCommand())
    ->register(new RouteCacheCommand())
    ->register(new RouteBootstrapRegressionCommand())
    ->register(new RouteClearCommand())
    ->register(new RouteLintCommand())
    ->register(new RouteListCommand())
    ->register(new ScheduleRunCommand())
    ->register(new ScheduleListCommand())
    ->register(new StorageCleanCommand())
    ->register(new MakeCommandCommand())
    ->register(new MakeControllerCommand())
    ->register(new MakeCrudCommand())
    ->register(new MakeMigrationCommand())
    ->register(new MakeModelCommand())
    ->register(new MakeMiddlewareCommand())
    ->register(new MakeModuleCommand())
    ->register(new MakePolicyCommand())
    ->register(new MakeRequestCommand())
    ->register(new MigrateCommand())
    ->register(new MigrateRollbackCommand())
    ->register(new MigrateStatusCommand())
    ->register(new SensitivitySmokeCommand())
    ->register(new TemporalSmokeCommand())
    ->register(new SecurityCheckCommand())
    ->register(new SecurityRegressionCommand());

// Run
try {
    $kernel   = new CliKernel();
    $exitCode = $kernel->run($argv);
} catch (Throwable $e) {
    echo PHP_EOL;
    echo "\033[31m[ERROR]\033[0m " . $e->getMessage() . PHP_EOL;
    echo '  ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL . PHP_EOL;
    $exitCode = 1;
}

exit($exitCode);
