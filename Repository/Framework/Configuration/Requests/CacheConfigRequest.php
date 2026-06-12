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

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates and resolves cache settings from the setup surface.
 *
 * @package Catalyst\Repository\Configuration\Requests
 * Responsibility: Restricts cache activation to production and converts submitted cache flags to booleans.
 */
final class CacheConfigRequest extends AbstractSettingsRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Returns validation rules for cache settings.
     *
     * Responsibility: Returns validation rules for cache settings.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'cache_enabled' => 'required|in:0,1',
            'cache_driver' => 'required|in:file,array,null',
            'cache_prefix' => 'max:64',
            'app_cache' => 'required|in:0,1',
            'config_cache' => 'required|in:0,1',
            'discovery_cache' => 'required|in:0,1',
            'route_cache' => 'required|in:0,1',
        ];
    }

    /**
     * Returns the cache-specific validation failure message.
     *
     * Responsibility: Returns the cache-specific validation failure message.
     */
    public function validationMessage(): string
    {
        return 'Cache activation is blocked outside production.';
    }

    /**
     * Returns the resolved cache payload after validation.
     *
     * Responsibility: Returns the resolved cache payload after validation.
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->resolvedData === null) {
            $this->validateResolved();
        }

        return $this->resolvedData ?? [];
    }

    /**
     * Authorizes, validates and resolves the cache payload.
     *
     * Responsibility: Authorizes, validates and resolves the cache payload.
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden('This request is not authorized.');
        }

        $this->prepareForValidation();

        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];

        if (ConfigManager::getInstance()->getEnvironment() !== 'production') {
            $errors['cache_enabled'][] = 'Framework cache can only be configured in production.';
        }

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = [
            'cache_enabled' => $this->toBoolean($data['cache_enabled'] ?? '0'),
            'cache_driver' => (string) ($data['cache_driver'] ?? 'file'),
            'cache_prefix' => (string) ($data['cache_prefix'] ?? ''),
            'app_cache' => $this->toBoolean($data['app_cache'] ?? '0'),
            'config_cache' => $this->toBoolean($data['config_cache'] ?? '0'),
            'discovery_cache' => $this->toBoolean($data['discovery_cache'] ?? '0'),
            'route_cache' => $this->toBoolean($data['route_cache'] ?? '0'),
        ];
    }

    /**
     * Builds normalized cache input for validation.
     *
     * Responsibility: Builds normalized cache input for validation.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'cache_enabled' => (string) $this->input('cache_enabled', '0'),
            'cache_driver' => $this->lowerStringInput('cache_driver', 'file'),
            'cache_prefix' => $this->stringInput('cache_prefix', 'catalyst_'),
            'app_cache' => (string) $this->input('app_cache', '0'),
            'config_cache' => (string) $this->input('config_cache', '0'),
            'discovery_cache' => (string) $this->input('discovery_cache', '0'),
            'route_cache' => (string) $this->input('route_cache', '0'),
        ];
    }

    /**
     * Converts a checkbox-like value to a boolean.
     *
     * Responsibility: Converts a checkbox-like value to a boolean.
     */
    private function toBoolean(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }
}
