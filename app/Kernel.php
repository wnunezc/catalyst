<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Kernel.php
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * Kernel component for the Catalyst Framework
 *
 */

namespace Catalyst;

use Catalyst\Framework\Authorization\Gate;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\MethodNotAllowedException;
use Catalyst\Helpers\Exceptions\RouteNotFoundException;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Middleware\SecurityHeadersMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Route\GlobalMiddlewareRegistrar;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\View\ModuleViewPathRegistrar;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Kernel - Core application bootstrapper
 *
 * Responsible for initializing, configuring and running
 * the Catalyst PHP Framework application.
 *
 * @package   Catalyst
 * @subpackage Core
 * @version   1.0.0
 * @since     1.0.0
 */
class Kernel
{
    use SingletonTrait;

    /**
     * @var bool Flag indicating if the framework has been bootstrapped
     */
    protected bool $bootstrapped = false;

    /**
     * @var Logger The logger instance
     */
    protected Logger $logger;

    /**
     * @var Request The HTTP request instance
     */
    protected Request $request;

    protected bool $debugEnabled = false;

    /**
     * Bootstrap the framework
     *
     * Initializes all core components and prepares the application to run
     *
     * @return self
     * @throws Exception
     */
    public function bootstrap(): self
    {
        if ($this->bootstrapped) {
            return $this;
        }

        try {
            // Get references to already initialized components
            $this->logger = Logger::getInstance();
            $this->request = Request::getInstance();

            $this->applyRuntimeConfiguration();

            // Log bootstrap start
            $this->logger->debug('Kernel bootstrap started', [
                'environment' => ConfigManager::getInstance()->getEnvironment(),
                'debug' => $this->debugEnabled,
            ]);

            SessionManager::getInstance()->init();

            // Initialize i18n system (after session — getLocale() reads session)
            Translator::getInstance()->init(
                (string)(ConfigManager::getInstance()->entry('app', 'project')['project_lang'] ?? 'en'),
                implode(DS, [PD, 'boot-core', 'lang'])
            );

            PermissionRegistry::getInstance()->registerGateDefinitions(Gate::getInstance());

            // Initialize router and load routes
            $this->loadRoutes();

            // Initialize session handler (when implemented)
            // $this->session = Session::getInstance();

            // Initialize database connection (when implemented)
            // $this->database = Database::getInstance();

            // Initialize view engine (when implemented)
            // $this->view = View::getInstance();


            // Mark as bootstrapped
            $this->bootstrapped = true;

            // Log bootstrap completion
            $this->logger->debug('Kernel bootstrap completed');

            return $this;
        } catch (Exception $e) {
            $this->logger->error('Kernel bootstrap failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Run the application
     *
     * Process the request and generate a response
     *
     * @return void
     * @throws Exception If the application hasn't been bootstrapped
     */
    public function run(): void
    {
        if (!$this->bootstrapped) {
            throw new Exception('Application must be bootstrapped before running');
        }

        $this->logger->debug('Application execution started');

        // SecurityHeadersMiddleware wraps ALL responses including errors (G12)
        $securityHeaders = new SecurityHeadersMiddleware();
        $response = $securityHeaders->process(
            $this->request,
            function (Request $request): Response {
                return $this->dispatchRequest($request);
            }
        );

        $response->send();
    }

    /**
     * Dispatch the request and handle routing errors internally,
     * so all responses (including 404/405/500) flow through middleware.
     *
     * @param Request $request
     * @return Response
     * @throws Exception Only in development for unhandled exceptions
     */
    protected function dispatchRequest(Request $request): Response
    {
        try {
            return Router::getInstance()->dispatch($request);

        } catch (RouteNotFoundException $e) {
            $this->logger->warning('Route not found', [
                'uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ]);
            return $this->buildNotFoundResponse($e);

        } catch (MethodNotAllowedException $e) {
            $this->logger->warning('Method not allowed', [
                'uri' => $request->getUri(),
                'method' => $request->getMethod(),
                'allowed_methods' => $e->getAllowedMethods(),
            ]);
            return $this->buildMethodNotAllowedResponse($e);

        } catch (ForbiddenException $e) {
            $this->logger->warning('Forbidden request', [
                'uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ]);
            return $this->buildForbiddenResponse($e);

        } catch (Exception $e) {
            $ticket = number_format(microtime(true), 4, '.', '');
            $this->logger->error('Application execution failed', [
                'ticket' => $ticket,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->debugEnabled) {
                throw $e;
            }

            return $this->buildServerErrorResponse($ticket);
        }
    }

    /**
     * Build a 404 Not Found response
     *
     * @param RouteNotFoundException $e
     * @return Response
     */
    protected function buildNotFoundResponse(RouteNotFoundException $e): Response
    {
        return ErrorResponseFactory::render(
            404,
            __('ui.errors.404_title'),
            __('ui.errors.404_message')
        );
    }


    /**
     * Build a 405 Method Not Allowed response
     *
     * @param MethodNotAllowedException $e
     * @return Response
     */
    protected function buildMethodNotAllowedResponse(MethodNotAllowedException $e): Response
    {
        return ErrorResponseFactory::render(
            405,
            __('ui.errors.405_title'),
            __('ui.errors.405_message'),
            ['Allow' => implode(', ', $e->getAllowedMethods())]
        );
    }


    /**
     * Build a 500 Server Error response
     *
     * @param string $ticket Correlated error ticket (matches log entry)
     * @return Response
     */
    protected function buildServerErrorResponse(string $ticket): Response
    {
        return ErrorResponseFactory::render(
            500,
            __('ui.errors.500_title'),
            __('ui.errors.500_message'),
            [],
            $ticket
        );
    }

    protected function buildForbiddenResponse(ForbiddenException $e): Response
    {
        return ErrorResponseFactory::forbidden(__('ui.errors.403_message'));
    }

    /**
     * Load application routes
     *
     * Loading order:
     *   1. boot-core/routes/global-routes.php        — canonical redirects + core actions
     *   2. boot-core/routes/api.php                  — global API routes (if present)
     *   3. Repository/Framework/{Module}/routes.php  — framework module routes (glob)
     *   4. Repository/App/Surface/{Module}/routes.php        — application module routes (glob)
     *
     * Global middleware and module view paths are registered before the cache
     * branch so cold and cached bootstraps receive the same transversal setup.
     *
     * @return void
     */
    protected function loadRoutes(): void
    {
        $router = Router::getInstance();
        (new GlobalMiddlewareRegistrar())->register($router);
        (new ModuleViewPathRegistrar())->register(View::getInstance(), ModuleRegistry::getInstance()->active());

        $cacheConfig = CacheSettings::current();
        $routeCacheEnabled = CacheSettings::featureEnabled('route_cache', $cacheConfig);

        if ($routeCacheEnabled && $router->loadCachedRoutes()) {
            $this->logger->info('Routes loaded from cache');
            return;
        }

        foreach (CliRouteLoader::routeFiles() as $routeFile) {
            require_once $routeFile;

            $isGlobal = str_contains($routeFile, implode(DS, ['boot-core', 'routes']));
            $message = $isGlobal ? 'Global route surface loaded' : 'Module route surface loaded';
            $this->logger->debug($message, ['path' => $routeFile]);
        }

        if ($routeCacheEnabled) {
            if ($router->cacheRoutes()) {
                $this->logger->info('Routes cached after cold bootstrap', [
                    'path' => $router->getCacheFile(),
                ]);
            } else {
                $this->logger->warning('Route cache rebuild skipped after cold bootstrap', [
                    'reason' => 'closure handler detected or cache write failed',
                ]);
            }
        }
    }

    /**
     * Display welcome page
     *
     * @return void
     * @throws Exception
     */
    protected function showWelcome(): void
    {
        $templatePath = implode(DS, [PD, 'boot-core', 'template', 'welcome.php']);
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            $this->logger->error('Welcome template not found', ['path' => $templatePath]);
            echo "<h1>Welcome to Catalyst Framework</h1>";
            echo "<p>Template file not found: $templatePath</p>";
        }
    }

    /**
     * Get generic error content for production environment.
     * The $ticket matches the entry written to the error log (G13).
     *
     * @param string $title  Error title
     * @param string|null $ticket Correlated log ticket
     * @return string HTML content
     */
    protected function getProductionErrorContent(string $title, ?string $ticket = null): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTicket = htmlspecialchars((string) ($ticket ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ticketHtml = $safeTicket !== '' ? '<p>Error ID: ' . $safeTicket . '</p>' : '';

        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $safeTitle . '</title><link href="/assets/css/catalyst/error-surface.css" rel="stylesheet"></head><body class="catalyst-error-shell-body"><main class="catalyst-error-shell"><section class="catalyst-error-card"><h1>' . $safeTitle . '</h1><p>Sorry, an error occurred while processing your request.</p>' . $ticketHtml . '</section></main></body></html>';
    }


    protected function applyRuntimeConfiguration(): void
    {
        $config   = ConfigManager::getInstance();
        $app      = $config->entry('app', 'project');
        $logging  = $config->entry('logging', 'logging');
        $timezone = trim((string)($app['project_timezone'] ?? 'UTC'));

        $this->debugEnabled = (bool)($app['project_debug'] ?? false);

        $maxFileSizeMb = (int)($logging['log_max_file_size_mb'] ?? 10);
        $maxRotatedFiles = (int)($logging['log_max_rotated_files'] ?? 7);

        $this->logger->configure([
            'logChannel'          => strtolower((string)($logging['log_channel'] ?? 'single')),
            'minimumLogLevel'     => strtolower((string)($logging['log_level'] ?? (IS_DEVELOPMENT ? 'debug' : 'error'))),
            'displayLogs'         => (bool)($logging['display_logs'] ?? false),
            'logRotationEnabled'  => (bool)($logging['log_rotation_enabled'] ?? true),
            'maxFileSizeBytes'    => max(1, $maxFileSizeMb) * 1024 * 1024,
            'maxRotatedFiles'     => max(1, $maxRotatedFiles),
        ]);

        if ($timezone !== '' && !date_default_timezone_set($timezone)) {
            $this->logger->warning('Invalid project timezone in app config; keeping current timezone', [
                'timezone' => $timezone,
            ]);
        }
    }
}
