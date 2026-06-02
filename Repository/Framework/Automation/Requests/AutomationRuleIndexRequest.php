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

namespace Catalyst\Repository\Automation\Requests;

use Catalyst\Framework\Http\FormRequest;

/**
 * Normalizes automation rule listing filters.
 *
 * @package Catalyst\Repository\Automation\Requests
 * Responsibility: Expose the allowed listing inputs and convert them into bounded repository criteria.
 */
final class AutomationRuleIndexRequest extends FormRequest
{
    /**
     * Returns the listing inputs accepted from the request.
     *
     * Responsibility: Returns the listing inputs accepted from the request.
     * @return string[]
     */
    public function only(): array
    {
        return ['page', 'per_page', 'search', 'trigger_type', 'state', 'temporal_state'];
    }

    /**
     * Declares listing validation rules.
     *
     * Responsibility: Declares listing validation rules.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Normalizes pagination and filter values for repository search.
     *
     * Responsibility: Normalizes pagination and filter values for repository search.
     * @return array<string, mixed>
     */
    public function criteria(): array
    {
        $data = $this->validated();

        return [
            'page' => max(1, (int) ($data['page'] ?? 1)),
            'per_page' => min(100, max(1, (int) ($data['per_page'] ?? 20))),
            'search' => trim((string) ($data['search'] ?? '')),
            'trigger_type' => trim((string) ($data['trigger_type'] ?? '')),
            'state' => trim((string) ($data['state'] ?? '')),
            'temporal_state' => trim((string) ($data['temporal_state'] ?? '')),
        ];
    }
}
