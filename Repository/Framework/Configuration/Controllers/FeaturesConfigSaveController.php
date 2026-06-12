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

namespace Catalyst\Repository\Configuration\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Configuration\Requests\FeaturesConfigRequest;
use Catalyst\Repository\Configuration\Support\FeaturesConfigWriter;

/**
 * Persists setup-owned feature switch defaults.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Delegates validated feature switch writes and returns the setup AJAX response.
 */
final class FeaturesConfigSaveController extends Controller
{
    /**
     * Initializes the Features Config Save Controller instance.
     *
     * Responsibility: Connects request validation and feature configuration persistence collaborators.
     */
    public function __construct(
        private readonly FeaturesConfigWriter $writer = new FeaturesConfigWriter()
    ) {
        parent::__construct();
    }

    /**
     * Saves validated feature switch settings.
     *
     * Responsibility: Persists setup feature switches after centralized request validation.
     */
    public function saveFeatures(FeaturesConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}