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
 * Reads a normalized feature-flag default state from an HTTP request.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Supplies the requested default flag state to the controller.
 */
final class FeatureFlagDefaultRequest
{
    use NormalizesCheckboxValues;

    /**
     * Wraps the incoming HTTP request used to read flag state.
     *
     * Responsibility: Wraps the incoming HTTP request used to read flag state.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Returns the requested enabled state for a feature flag.
     *
     * Responsibility: Returns the requested enabled state for a feature flag.
     */
    public function enabled(): bool
    {
        return $this->checkboxValue($this->request->input('enabled'));
    }
}
