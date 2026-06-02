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
 * Reads locale synchronization input from an HTTP request.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Supplies normalized locale synchronization fields to the controller.
 */
final class LocaleSyncRequest
{
    use NormalizesCheckboxValues;

    /**
     * Wraps the incoming HTTP request used to read synchronization fields.
     *
     * Responsibility: Wraps the incoming HTTP request used to read synchronization fields.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Returns the locale code requested for synchronization.
     *
     * Responsibility: Returns the locale code requested for synchronization.
     */
    public function locale(): string
    {
        return trim((string) $this->request->input('target_locale', ''));
    }

    /**
     * Returns whether locale synchronization should run as a preview.
     *
     * Responsibility: Returns whether locale synchronization should run as a preview.
     */
    public function dryRun(): bool
    {
        return $this->checkboxValue($this->request->input('dry_run_sync'));
    }
}
