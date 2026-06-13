<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Auth\RememberMe;
use Catalyst\Framework\Auth\TokenRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cache\FileCacheStore;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\View\HtmlAllowlistSanitizer;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use RuntimeException;
use Throwable;
use TypeError;

/**
 * security:regression CLI command.
 *
 * Responsibility: Runs the security:regression command to Run focused regressions for inline JSON, reset/remember and signed local cache payloads.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class SecurityRegressionCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the result as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'security:regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Run focused regressions for inline JSON, reset/remember and signed local cache payloads';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $result = [
            'success' => false,
            'steps' => [],
        ];

        try {
            $result['steps'][] = $this->assertInlineJsonEscaping();
            $result['steps'][] = $this->assertTrustedHtmlContract();
            $result['steps'][] = $this->assertPrivateRuntimeStorage();
            $result['steps'][] = $this->assertHtmlAllowlistSanitizer();
            $result['steps'][] = $this->assertRememberTokenInvalidation();
            $result['steps'][] = $this->assertSignedFileCachePayloads();
            $result['steps'][] = $this->assertRouteCacheMiddlewareSigning();

            foreach ($result['steps'] as $step) {
                if (($step['status'] ?? '') !== 'ok') {
                    throw new RuntimeException('Security regression failed at step: ' . (string) ($step['step'] ?? 'unknown'));
                }
            }

            $result['success'] = true;
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Security Regression');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-28s %s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Security regression passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Security regression failed.'));

        return 1;
    }

    /**
     * Describes the assert inline json escaping helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert inline json escaping helper workflow used by this CLI component.
     */
    private function assertInlineJsonEscaping(): array
    {
        $payload = '</script><script>alert(1)</script>';
        $encoded = InlineJson::encode(['payload' => $payload]);
        $expectedFragment = '\u003C/script\u003E\u003Cscript\u003Ealert(1)\u003C/script\u003E';

        return [
            'step' => 'inline-json-escaping',
            'status' => !str_contains($encoded, '</script>') && str_contains($encoded, $expectedFragment) ? 'ok' : 'failed',
            'encoded' => $encoded,
        ];
    }

    /**
     * Describes the assert trusted html contract helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert trusted html contract helper workflow used by this CLI component.
     */
    private function assertTrustedHtmlContract(): array
    {
        $response = JsonResponse::api(['probe' => true], true, 'ok')->withHtml(
            '#security-probe',
            TrustedHtml::fromString('<div class="probe">ok</div>')
        );

        $data = $response->getData();
        $rejectsRawString = false;

        try {
            /** @phpstan-ignore-next-line */
            JsonResponse::api()->withHtml('#security-probe', '<div>unsafe</div>');
        } catch (TypeError) {
            $rejectsRawString = true;
        }

        return [
            'step' => 'trusted-html-contract',
            'status' => (($data['html_policy'] ?? '') === JsonResponse::HTML_POLICY_TRUSTED) && $rejectsRawString ? 'ok' : 'failed',
        ];
    }

    /**
     * Describes the assert private runtime storage helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert private runtime storage helper workflow used by this CLI component.
     */
    private function assertPrivateRuntimeStorage(): array
    {
        $storage = StorageManager::getInstance();
        $path = 'security-regression/runtime-' . bin2hex(random_bytes(6)) . '.txt';

        try {
            $storedPath = $storage->put($path, 'private-runtime-probe', 'runtime');

            return [
                'step' => 'private-runtime-storage',
                'status' => $storage->exists($storedPath, 'runtime')
                    && $storage->url($storedPath, 'runtime') === ''
                    && !is_file(PD . DS . 'public' . DS . str_replace('/', DS, $storedPath))
                    ? 'ok'
                    : 'failed',
            ];
        } finally {
            try {
                $storage->delete($path, 'runtime');
            } catch (Throwable) {
                // The regression must report the original storage failure.
            }
        }
    }

    /**
     * Describes the assert html allowlist sanitizer helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert html allowlist sanitizer helper workflow used by this CLI component.
     */
    private function assertHtmlAllowlistSanitizer(): array
    {
        $sanitizer = new HtmlAllowlistSanitizer();
        $sanitized = $sanitizer->sanitize(
            '<article class="probe" style="color:red" onclick="alert(1)">'
            . '<a href="javascript:alert(1)">unsafe</a>'
            . '<a href="https://example.com/path">safe</a>'
            . '<script>alert(1)</script>'
            . '<strong>ok</strong>'
            . '</article>'
        );

        return [
            'step' => 'html-allowlist-sanitizer',
            'status' => str_contains($sanitized, '<article class="probe">')
                && str_contains($sanitized, '<a>unsafe</a>')
                && str_contains($sanitized, '<a href="https://example.com/path">safe</a>')
                && str_contains($sanitized, '<strong>ok</strong>')
                && !str_contains($sanitized, '<script')
                && !str_contains($sanitized, 'alert(1)')
                && !str_contains($sanitized, 'onclick')
                && !str_contains($sanitized, 'style=')
                && !str_contains($sanitized, 'javascript:')
                ? 'ok'
                : 'failed',
        ];
    }

    /**
     * Describes the assert remember token invalidation helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert remember token invalidation helper workflow used by this CLI component.
     */
    private function assertRememberTokenInvalidation(): array
    {
        $db = DatabaseManager::getInstance()->connection();
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $user = $db->selectOne(
            'SELECT id
             FROM users
             WHERE tenant_id = ?
               AND active = 1
               AND email_verified = 1
             ORDER BY id ASC
             LIMIT 1',
            [$tenantId]
        );

        if ($user === null) {
            throw new RuntimeException('security:regression requires one active verified user.');
        }

        $userId = (int) ($user['id'] ?? 0);
        $pdo = $db->getPdo();
        $pdo->beginTransaction();

        try {
            $db->execute(
                'INSERT INTO remember_tokens (user_id, token_hash, active, expires_at, created_at)
                 VALUES (?, ?, 1, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY), UTC_TIMESTAMP())',
                [$userId, hash('sha256', 'security-regression-' . bin2hex(random_bytes(8)))]
            );

            $rawResetToken = TokenRepository::getInstance()->createPasswordResetToken($userId);
            $consumedUserId = TokenRepository::getInstance()->consumePasswordResetToken($rawResetToken);
            UserProvider::getInstance()->updatePassword($userId, 'Regression#2026!Password');
            RememberMe::getInstance()->invalidate($userId);

            $activeTokenRow = $db->selectOne(
                'SELECT COUNT(*) AS total
                 FROM remember_tokens
                 WHERE user_id = ?
                   AND active = 1',
                [$userId]
            );

            return [
                'step' => 'reset-invalidates-remember',
                'status' => $consumedUserId === $userId && ((int) ($activeTokenRow['total'] ?? 0)) === 0 ? 'ok' : 'failed',
            ];
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    /**
     * Describes the assert signed file cache payloads helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert signed file cache payloads helper workflow used by this CLI component.
     */
    private function assertSignedFileCachePayloads(): array
    {
        $cacheRoot = PD . DS . 'boot-core' . DS . 'storage' . DS . 'security-regression-cache';
        $store = new FileCacheStore($cacheRoot, 'security_regression');
        $cacheKey = 'signed_payload';
        $store->clear();

        $value = ['trusted' => TrustedHtml::fromString('<strong>ok</strong>')];
        $store->put($cacheKey, $value, 600);

        $restored = $store->get($cacheKey);
        $path = $this->cachePathForKey($cacheRoot, 'security_regression', $cacheKey);
        $contents = is_file($path) ? (string) file_get_contents($path) : '';

        if ($contents === '') {
            return [
                'step' => 'signed-file-cache',
                'status' => 'failed',
            ];
        }

        $replacementCount = 0;
        $tamperedContents = str_replace("'signature' => '", "'signature' => 'tampered-", $contents, $replacementCount);

        if ($replacementCount < 1) {
            return [
                'step' => 'signed-file-cache',
                'status' => 'failed',
            ];
        }

        file_put_contents($path, $tamperedContents);
        $tampered = $store->get($cacheKey, 'tampered-default');
        $store->clear();

        return [
            'step' => 'signed-file-cache',
            'status' => (($restored['trusted'] ?? null) instanceof TrustedHtml) && $tampered === 'tampered-default' ? 'ok' : 'failed',
        ];
    }

    /**
     * Describes the assert route cache middleware signing helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the assert route cache middleware signing helper workflow used by this CLI component.
     */
    private function assertRouteCacheMiddlewareSigning(): array
    {
        $route = new Route(['GET'], '/security-regression', static fn (): null => null);
        $route->middleware(new RoleMiddleware(permissions: 'manage-operations-deployments'));
        $route->normalizeMiddlewareForCache();

        $state = [
            'methods' => $route->getMethods(),
            'pattern' => $route->getPattern(),
            'handler' => $route->getHandler(),
            'name' => $route->getName(),
            'middleware' => $route->getMiddleware(),
            'constraints' => $route->getConstraints(),
            'namespace' => $route->getNamespace(),
            'attributes' => [],
        ];

        $serializedMiddleware = $state['middleware'][0] ?? null;
        if (!is_array($serializedMiddleware) || !isset($serializedMiddleware['__serialized_middleware']['signature'])) {
            return [
                'step' => 'signed-route-cache',
                'status' => 'failed',
            ];
        }

        $tamperedState = $state;
        $tamperedState['middleware'][0]['__serialized_middleware']['signature'] = 'tampered-' . (string) $tamperedState['middleware'][0]['__serialized_middleware']['signature'];
        $restored = Route::__set_state($tamperedState);
        $restoredMiddleware = $restored->getMiddleware()[0] ?? null;

        return [
            'step' => 'signed-route-cache',
            'status' => !($restoredMiddleware instanceof RoleMiddleware) ? 'ok' : 'failed',
        ];
    }

    /**
     * Describes the cache path for key helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the cache path for key helper workflow used by this CLI component.
     */
    private function cachePathForKey(string $baseDirectory, string $prefix, string $key): string
    {
        $normalizedPrefix = preg_replace('/[^a-zA-Z0-9_-]+/', '_', trim($prefix)) ?: 'catalyst_';
        $normalizedKey = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $key) ?: 'cache_key';
        $hash = sha1($key);

        return rtrim($baseDirectory, '\\/') . DS . $normalizedPrefix . DS . $normalizedKey . '_' . $hash . '.php';
    }
}
