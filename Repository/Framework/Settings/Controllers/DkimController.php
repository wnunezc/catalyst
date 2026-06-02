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

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Mail\DkimGenerator;
use RuntimeException;

/**************************************************************************************
 * DkimController — AJAX endpoint for RSA DKIM key generation.
 *
 * Routes:
 *   POST /setup/dkim/generate → generate()
 *
 * @package Catalyst\Repository\Settings\Controllers
 **************************************************************************************/
/**
 * Defines the Dkim Controller class contract.
 *
 * @package Catalyst\Repository\Settings\Controllers
 * Responsibility: Coordinates the dkim controller behavior within its module boundary.
 */
class DkimController extends Controller
{
    /**
     * Generate a DKIM RSA key pair and return the DNS TXT record.
     *
     * @param Request $request
     * @return Response
     */
    public function generate(Request $request): Response
    {
        $data = [
            'dkim_domain'     => trim((string)$request->input('dkim_domain', '')),
            'dkim_selector'   => trim((string)$request->input('dkim_selector', '')),
            'dkim_connection' => trim((string)$request->input('dkim_connection', 'mail1')),
        ];

        $validator = $this->validate($data, [
            'dkim_domain'   => 'required|max:255',
            'dkim_selector' => 'required|max:63',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        try {
            $result = DkimGenerator::getInstance()->generateKeys(
                $data['dkim_domain'],
                $data['dkim_selector'],
                $data['dkim_connection']
            );
        } catch (RuntimeException $e) {
            return $this->jsonErrorWithToast($e->getMessage(), 500);
        }

        return $this->jsonSuccessWithToast($result, __('settings.messages.dkim_generated'));
    }
}
