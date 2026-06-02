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

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Repository\Operations\Requests\Concerns\NormalizesCheckboxValues;

/**
 * Defines the Locale Create Request class contract.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Coordinates the locale create request behavior within its module boundary.
 */
final class LocaleCreateRequest
{
    use NormalizesCheckboxValues;

    /**
     * Initializes the Locale Create Request instance.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Handles the locale workflow.
     */
    public function locale(): string
    {
        return trim((string) $this->request->input('locale_code', ''));
    }

    /**
     * Handles the label workflow.
     */
    public function label(): string
    {
        return trim((string) $this->request->input('locale_label', ''));
    }

    /**
     * Handles the dry run workflow.
     */
    public function dryRun(): bool
    {
        return $this->checkboxValue($this->request->input('dry_run'));
    }
}
