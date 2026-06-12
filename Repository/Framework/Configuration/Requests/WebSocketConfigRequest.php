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

/**
 * Validates WebSocket settings from the setup surface.
 *
 * @package Catalyst\Repository\Configuration\Requests
 * Responsibility: Defines WebSocket bind, publisher and port constraints and normalizes submitted values.
 */
final class WebSocketConfigRequest extends AbstractSettingsRequest
{
    /**
     * Returns validation rules for WebSocket settings.
     *
     * Responsibility: Returns validation rules for WebSocket settings.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'ws_port' => 'required|integer|min_value:1024|max_value:65535',
            'ws_host' => 'required|max:64',
            'ws_internal_port' => 'required|integer|min_value:1024|max_value:65535',
            'ws_publisher_url' => 'required|max:255',
        ];
    }

    /**
     * Builds normalized WebSocket input for validation.
     *
     * Responsibility: Builds normalized WebSocket input for validation.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'ws_port' => (string) $this->input('ws_port', '8080'),
            'ws_host' => $this->stringInput('ws_host', '0.0.0.0'),
            'ws_internal_port' => (string) $this->input('ws_internal_port', '8181'),
            'ws_publisher_url' => $this->stringInput('ws_publisher_url'),
            'ws_enabled' => $this->booleanFlag('ws_enabled', true),
        ];
    }
}
