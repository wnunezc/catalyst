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

namespace Catalyst\Repository\Workspaces\Documents\Requests;

use Catalyst\Framework\Http\FormRequest;

/**
 * Normalizes document template listing filters.
 *
 * @package Catalyst\Repository\Workspaces\Documents\Requests
 * Responsibility: Convert listing inputs into bounded repository criteria.
 */
final class DocumentTemplateIndexRequest extends FormRequest
{
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
     * @return array{page:int,per_page:int,search:string,format:string,state:string}
     */
    public function criteria(): array
    {
        return [
            'page' => max(1, (int) $this->input('page', 1)),
            'per_page' => min(100, max(1, (int) $this->input('per_page', 20))),
            'search' => trim((string) $this->input('search', '')),
            'format' => trim((string) $this->input('format', '')),
            'state' => trim((string) $this->input('state', '')),
        ];
    }
}
