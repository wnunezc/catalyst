<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Repository\Settings\Support\FtpConnectionProbe;
use Throwable;

final class FtpConfigController extends Controller
{
    public function __construct(
        private readonly FtpConnectionProbe $probe = new FtpConnectionProbe()
    ) {
        parent::__construct();
    }

    public function saveFtp(Request $request): Response
    {
        [$data, $validator] = $this->validatePayload($request);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        $cfg = ConfigManager::getInstance();
        $existing = $cfg->entry('ftp', 'ftp1');

        $cfg->writeSection('ftp', [
            'ftp1' => $this->buildPersistedPayload($request, $data, $existing),
        ]);

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }

    public function pretest(Request $request): Response
    {
        [$data, $validator] = $this->validatePayload($request);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        $cfg = ConfigManager::getInstance();
        $existing = $cfg->entry('ftp', 'ftp1');
        $payload = $this->buildPersistedPayload($request, $data, $existing);

        try {
            $result = $this->probe->pretest($payload);
        } catch (Throwable $e) {
            return $this->jsonErrorWithToast($e->getMessage(), 422);
        }

        $message = $result['cleanup_warning'] === null
            ? __('settings.messages.ftp_pretest_success')
            : __('settings.messages.ftp_pretest_success_cleanup_warning');

        return $this->jsonSuccessWithToast($result, $message);
    }

    /**
     * @return array{0: array<string, mixed>, 1: mixed}
     */
    private function validatePayload(Request $request): array
    {
        $data = [
            'ftp_protocol' => strtolower(trim((string) $request->input('ftp_protocol', 'ftp'))),
            'ftp_host' => trim((string) $request->input('ftp_host', '')),
            'ftp_port' => (string) $request->input('ftp_port', '21'),
            'ftp_username' => trim((string) $request->input('ftp_username', '')),
            'ftp_root' => trim((string) $request->input('ftp_root', '/')),
            'ftp_timeout' => (string) $request->input('ftp_timeout', '10'),
        ];

        $validator = $this->validate($data, [
            'ftp_protocol' => 'required|in:ftp,ftps,sftp',
            'ftp_host' => 'required|max:255',
            'ftp_port' => 'required|integer|min_value:1|max_value:65535',
            'ftp_username' => 'required|max:255',
            'ftp_root' => 'required|max:255',
            'ftp_timeout' => 'required|integer|min_value:1|max_value:120',
        ]);

        return [$data, $validator];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $existing
     * @return array<string, mixed>
     */
    private function buildPersistedPayload(Request $request, array $data, array $existing): array
    {
        $password = trim((string) $request->input('ftp_password', ''));
        $protocol = (string) $data['ftp_protocol'];
        $port = (int) $data['ftp_port'];

        return [
            'ftp_protocol' => $protocol,
            'ftp_host' => (string) $data['ftp_host'],
            'ftp_port' => $port,
            'ftp_username' => (string) $data['ftp_username'],
            'ftp_password' => $password !== '' ? $password : (string) ($existing['ftp_password'] ?? ''),
            'ftp_root' => $this->normalizeRoot((string) $data['ftp_root']),
            'ftp_timeout' => (int) $data['ftp_timeout'],
            'ftp_ssl' => $protocol === 'ftps',
            'ftp_passive' => $this->booleanFlag($request, 'ftp_passive'),
        ];
    }

    private function booleanFlag(Request $request, string $key, bool $default = false): bool
    {
        return in_array((string) $request->input($key, $default ? '1' : '0'), ['1', 'true', 'on', 'yes'], true);
    }

    private function normalizeRoot(string $root): string
    {
        $trimmed = trim($root);

        if ($trimmed === '') {
            return '/';
        }

        $normalized = '/' . ltrim(str_replace('\\', '/', $trimmed), '/');

        return rtrim($normalized, '/') ?: '/';
    }
}
