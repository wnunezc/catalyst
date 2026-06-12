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
use Catalyst\Framework\Mail\DkimGenerator;
use Catalyst\Repository\Configuration\Requests\DkimGenerateRequest;
use RuntimeException;

/**
 * Generates DKIM key material for the mail setup surface.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Validates DKIM input, invokes key generation and returns the DNS record payload.
 */
class DkimController extends Controller
{
    /**
     * Generate a DKIM RSA key pair and return the DNS TXT record.
     *
     * Responsibility: Generate a DKIM RSA key pair and return the DNS TXT record.
     * @param Request $request
     * @return Response
     */
    public function generate(DkimGenerateRequest $request): Response
    {
        $data = $request->validated();

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
