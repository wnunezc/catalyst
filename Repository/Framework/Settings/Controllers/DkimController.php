<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst\Repository\Settings\Controllers
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Repository
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 **************************************************************************************/

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
